<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\VacationTravelOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VacationTravelOptionController extends Controller
{
    public function store(Request $request, Vacation $vacation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateTravelOption($request, $vacation, 'travelOptionNew');

        $vacation->travelOptions()->create([
            'user_id' => $user->id,
            'vacation_ski_area_id' => $validated['vacation_ski_area_id'] ?? null,
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'price_per_person' => $validated['price_per_person'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('vacations.travel-planner', $vacation)->with('status', 'travel-option-added');
    }

    public function update(Request $request, Vacation $vacation, VacationTravelOption $travelOption): RedirectResponse
    {
        $user = $request->user();

        abort_unless($travelOption->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $travelOption->user_id === $user->id, 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateTravelOption($request, $vacation, 'travelOption'.$travelOption->id);

        $travelOption->update([
            'vacation_ski_area_id' => $validated['vacation_ski_area_id'] ?? null,
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'price_per_person' => $validated['price_per_person'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('vacations.travel-planner', $vacation)->with('status', 'travel-option-updated');
    }

    public function destroy(Request $request, Vacation $vacation, VacationTravelOption $travelOption): RedirectResponse
    {
        $user = $request->user();

        abort_unless($travelOption->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $travelOption->user_id === $user->id, 403);

        $travelOption->delete();

        return redirect()->route('vacations.travel-planner', $vacation)->with('status', 'travel-option-removed');
    }

    /**
     * Eigen foutenzak per formulier, zodat een fout niet onder alle reisopties verschijnt.
     * De bestemming moet een skigebied van déze vakantie zijn.
     *
     * @return array<string, mixed>
     */
    private function validateTravelOption(Request $request, Vacation $vacation, string $errorBag): array
    {
        return $request->validateWithBag($errorBag, [
            'vacation_ski_area_id' => [
                'nullable',
                Rule::exists('vacation_ski_areas', 'id')->where('vacation_id', $vacation->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'price_per_person' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
