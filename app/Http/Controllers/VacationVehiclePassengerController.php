<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\VacationVehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VacationVehiclePassengerController extends Controller
{
    /**
     * Zet iemand in een auto. Iedereen mag zichzelf indelen; de eigenaar van de
     * auto en de beheerder mogen dat ook voor anderen doen.
     */
    public function store(Request $request, Vacation $vacation, VacationVehicle $vehicle): RedirectResponse
    {
        abort_unless($vehicle->vacation_id === $vacation->id, 404);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $request->validateWithBag('passengers'.$vehicle->id, [
            'user_id' => ['required', 'integer'],
        ]);

        $passengerId = (int) $validated['user_id'];

        abort_unless($vacation->users->contains('id', $passengerId), 422);
        abort_unless($this->mayAssign($request, $vacation, $vehicle, $passengerId), 403);

        if ($vehicle->seats !== null && $vehicle->passengers()->count() >= $vehicle->seats
            && ! $vehicle->passengers->contains('id', $passengerId)) {
            throw ValidationException::withMessages([
                'user_id' => 'Deze auto zit vol ('.$vehicle->seats.' zitplaatsen).',
            ])->errorBag('passengers'.$vehicle->id);
        }

        // Iemand kan maar in één auto tegelijk zitten: haal hem eerst uit de rest.
        $vacation->vehicles->each(fn (VacationVehicle $other) => $other->passengers()->detach($passengerId));

        $vehicle->passengers()->syncWithoutDetaching([$passengerId]);

        return redirect()
            ->route('vacations.travel-planner', $vacation)
            ->with('status', 'passengers-updated');
    }

    /**
     * Haal iemand weer uit de auto.
     */
    public function destroy(Request $request, Vacation $vacation, VacationVehicle $vehicle, int $passenger): RedirectResponse
    {
        abort_unless($vehicle->vacation_id === $vacation->id, 404);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);
        abort_unless($this->mayAssign($request, $vacation, $vehicle, $passenger), 403);

        $vehicle->passengers()->detach($passenger);

        return redirect()
            ->route('vacations.travel-planner', $vacation)
            ->with('status', 'passengers-updated');
    }

    private function mayAssign(Request $request, Vacation $vacation, VacationVehicle $vehicle, int $passengerId): bool
    {
        $user = $request->user();

        if ($user->isAdmin() || $vehicle->user_id === $user->id) {
            return true;
        }

        // Deelnemers regelen alleen hun eigen plek.
        return $passengerId === $user->id && $vacation->users->contains('id', $user->id);
    }
}
