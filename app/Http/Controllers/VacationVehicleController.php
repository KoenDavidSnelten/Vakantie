<?php

namespace App\Http\Controllers;

use App\Enums\VehicleType;
use App\Models\Vacation;
use App\Models\VacationVehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VacationVehicleController extends Controller
{
    public function store(Request $request, Vacation $vacation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateVehicle($request);
        $isRental = $validated['type'] === VehicleType::Rental->value;

        $vacation->vehicles()->create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'car_model' => $validated['car_model'],
            'seats' => $validated['seats'] ?? null,
            'has_winter_tires' => $request->boolean('has_winter_tires'),
            'has_large_trunk' => $request->boolean('has_large_trunk'),
            'has_roof_box' => $request->boolean('has_roof_box'),
            'price_per_day' => $isRental ? ($validated['price_per_day'] ?? null) : null,
        ]);

        return redirect()->route('vacations.travel-planner', $vacation)->with('status', 'vehicle-added');
    }

    public function update(Request $request, Vacation $vacation, VacationVehicle $vehicle): RedirectResponse
    {
        $user = $request->user();

        abort_unless($vehicle->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $vehicle->user_id === $user->id, 403);
        abort_unless($vacation->phase->hasPlannerAccess(), 403);

        $validated = $this->validateVehicle($request);
        $isRental = $validated['type'] === VehicleType::Rental->value;

        $vehicle->update([
            'type' => $validated['type'],
            'car_model' => $validated['car_model'],
            'seats' => $validated['seats'] ?? null,
            'has_winter_tires' => $request->boolean('has_winter_tires'),
            'has_large_trunk' => $request->boolean('has_large_trunk'),
            'has_roof_box' => $request->boolean('has_roof_box'),
            'price_per_day' => $isRental ? ($validated['price_per_day'] ?? null) : null,
        ]);

        return redirect()->route('vacations.travel-planner', $vacation)->with('status', 'vehicle-updated');
    }

    public function destroy(Request $request, Vacation $vacation, VacationVehicle $vehicle): RedirectResponse
    {
        $user = $request->user();

        abort_unless($vehicle->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $vehicle->user_id === $user->id, 403);

        $vehicle->delete();

        return redirect()->route('vacations.travel-planner', $vacation)->with('status', 'vehicle-removed');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateVehicle(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::enum(VehicleType::class)],
            'car_model' => ['required', 'string', 'max:255'],
            'seats' => ['nullable', 'integer', 'min:1', 'max:99'],
            'price_per_day' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
