<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\VacationSkiArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VacationSkiAreaController extends Controller
{
    public function index(Request $request, Vacation $vacation): View|RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        if (! $vacation->phase->hasPlannerAccess()) {
            return redirect()->route('vacations.show', $vacation);
        }

        $vacation->load([
            'users',
            'skiAreas.votes.user',
            'skiAreas.addedBy',
            'skiAreas.comments.user',
            'skiAreas.hotels.votes.user',
            'skiAreas.hotels.addedBy',
            'skiAreas.hotels.comments.user',
        ]);

        return view('vacations.locations', [
            'vacation' => $vacation,
        ]);
    }

    public function store(Request $request, Vacation $vacation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateSkiArea($request, 'skiAreaNew');

        $vacation->skiAreas()->create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'price_ski_pass' => $validated['price_ski_pass'] ?? null,
            'distance_to_slopes_km' => $validated['distance_to_slopes_km'] ?? null,
            'has_bus' => $request->boolean('has_bus'),
            'ski_area_map_url' => $validated['ski_area_map_url'] ?? null,
        ]);

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'ski-area-added');
    }

    public function update(Request $request, Vacation $vacation, VacationSkiArea $skiArea): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $skiArea->user_id === $user->id, 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateSkiArea($request, 'skiArea'.$skiArea->id);

        $skiArea->update([
            'name' => $validated['name'],
            'price_ski_pass' => $validated['price_ski_pass'] ?? null,
            'distance_to_slopes_km' => $validated['distance_to_slopes_km'] ?? null,
            'has_bus' => $request->boolean('has_bus'),
            'ski_area_map_url' => $validated['ski_area_map_url'] ?? null,
        ]);

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'ski-area-updated');
    }

    public function destroy(Request $request, Vacation $vacation, VacationSkiArea $skiArea): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $skiArea->user_id === $user->id, 403);

        $skiArea->delete();

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'ski-area-removed');
    }

    public function vote(Request $request, Vacation $vacation, VacationSkiArea $skiArea): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        $validated = $request->validate([
            'value' => ['required', Rule::in([1, -1])],
        ]);

        $vote = $skiArea->votes()->where('user_id', $user->id)->first();

        if ($vote && $vote->value === (int) $validated['value']) {
            $vote->delete();
        } elseif ($vote) {
            $vote->update(['value' => $validated['value']]);
        } else {
            $skiArea->votes()->create(['user_id' => $user->id, 'value' => $validated['value']]);
        }

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'vote-updated');
    }

    /**
     * Elk formulier op de locatiepagina valideert in zijn eigen foutenzak, anders
     * verschijnt een fout van één formulier onder alle andere formulieren op de pagina.
     *
     * @return array<string, mixed>
     */
    private function validateSkiArea(Request $request, string $errorBag): array
    {
        return $request->validateWithBag($errorBag, [
            'name' => ['required', 'string', 'max:255'],
            'price_ski_pass' => ['nullable', 'numeric', 'min:0'],
            'distance_to_slopes_km' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'ski_area_map_url' => ['nullable', 'url', 'max:2048'],
        ]);
    }
}
