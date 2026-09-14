<?php

namespace App\Http\Controllers;

use App\Enums\PriceUnit;
use App\Models\Vacation;
use App\Models\VacationHotel;
use App\Models\VacationSkiArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class VacationHotelController extends Controller
{
    public function store(Request $request, Vacation $vacation, VacationSkiArea $skiArea): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateHotel($request, 'hotelNew'.$skiArea->id);
        $url = $validated['url'] ?? null;
        $manualImage = $validated['image_url'] ?? null;

        $skiArea->hotels()->create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'url' => $url,
            'image_url' => $manualImage ?: ($url ? $this->fetchOgImage($url) : null),
            'price_accommodation_per_night' => $validated['price_accommodation_per_night'] ?? null,
            'price_accommodation_unit' => $validated['price_accommodation_unit'] ?? PriceUnit::Total->value,
            'room_layout' => $validated['room_layout'] ?? null,
        ]);

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'hotel-added');
    }

    public function update(Request $request, Vacation $vacation, VacationSkiArea $skiArea, VacationHotel $hotel): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($hotel->vacation_ski_area_id === $skiArea->id, 404);
        abort_unless($user->isAdmin() || $hotel->user_id === $user->id, 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateHotel($request, 'hotel'.$hotel->id);
        $url = $validated['url'] ?? null;
        $manualImage = $validated['image_url'] ?? null;

        $hotel->update([
            'name' => $validated['name'],
            'url' => $url,
            'image_url' => $this->resolveImageUrlForUpdate($hotel, $manualImage, $url),
            'price_accommodation_per_night' => $validated['price_accommodation_per_night'] ?? null,
            'price_accommodation_unit' => $validated['price_accommodation_unit'] ?? PriceUnit::Total->value,
            'room_layout' => $validated['room_layout'] ?? null,
        ]);

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'hotel-updated');
    }

    public function destroy(Request $request, Vacation $vacation, VacationSkiArea $skiArea, VacationHotel $hotel): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($hotel->vacation_ski_area_id === $skiArea->id, 404);
        abort_unless($user->isAdmin() || $hotel->user_id === $user->id, 403);

        $hotel->delete();

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'hotel-removed');
    }

    public function vote(Request $request, Vacation $vacation, VacationSkiArea $skiArea, VacationHotel $hotel): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($hotel->vacation_ski_area_id === $skiArea->id, 404);
        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        $validated = $request->validate([
            'value' => ['required', Rule::in([1, -1])],
        ]);

        $vote = $hotel->votes()->where('user_id', $user->id)->first();

        if ($vote && $vote->value === (int) $validated['value']) {
            $vote->delete();
        } elseif ($vote) {
            $vote->update(['value' => $validated['value']]);
        } else {
            $hotel->votes()->create(['user_id' => $user->id, 'value' => $validated['value']]);
        }

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'vote-updated');
    }

    /**
     * Elk hotelformulier krijgt zijn eigen foutenzak: zonder dat zou een fout bij
     * één hotel de melding onder elk formulier op de pagina zetten.
     *
     * @return array<string, mixed>
     */
    private function validateHotel(Request $request, string $errorBag): array
    {
        return $request->validateWithBag($errorBag, [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'price_accommodation_per_night' => ['nullable', 'numeric', 'min:0'],
            'price_accommodation_unit' => ['nullable', Rule::enum(PriceUnit::class)],
            'room_layout' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /**
     * A manually supplied photo always wins. Otherwise, only re-scrape when the
     * link actually changed (or nothing was found before), no point re-fetching
     * on every edit.
     */
    private function resolveImageUrlForUpdate(VacationHotel $hotel, ?string $manualImage, ?string $url): ?string
    {
        if ($manualImage) {
            return $manualImage;
        }

        if (! $url) {
            return null;
        }

        if ($url === $hotel->url && $hotel->image_url) {
            return $hotel->image_url;
        }

        return $this->fetchOgImage($url);
    }

    /**
     * Best-effort scrape of the page's Open Graph image, so a photo shows up
     * automatically for an Airbnb/hotel link. Returns null on any failure
     * (blocked, no og:image, unreachable, unsafe host), this is a nice-to-have,
     * never something the request should fail over. Some sites (e.g.
     * Booking.com) sit behind a bot-challenge and will never yield a result
     * this way; the "Foto URL" field lets people paste one in manually instead.
     */
    private function fetchOgImage(string $url): ?string
    {
        if (! $this->isSafeToFetch($url)) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->withUserAgent('Mozilla/5.0 (compatible; VakantiePlanner/1.0)')
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        // og:image, allowing either attribute order.
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $response->body(), $matches)
            || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $response->body(), $matches)
        ) {
            $image = html_entity_decode($matches[1]);

            return filter_var($image, FILTER_VALIDATE_URL) ? $image : null;
        }

        return null;
    }

    /**
     * Guards against SSRF: only fetch plain http(s) URLs that resolve to a
     * public IP, since this URL is user-submitted and fetched server-side.
     */
    private function isSafeToFetch(string $url): bool
    {
        $parts = parse_url($url);

        if (! $parts || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
            return false;
        }

        $ip = filter_var($parts['host'], FILTER_VALIDATE_IP) ? $parts['host'] : gethostbyname($parts['host']);

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
