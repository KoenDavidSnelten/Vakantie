<?php

namespace App\Http\Controllers;

use App\Enums\VacationPhase;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Het dashboard is de landingspagina na inloggen en beantwoordt één vraag:
     * wat is de eerstvolgende reis en wat wordt er nú van mij verwacht?
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $vacations = ($user->isAdmin() ? Vacation::query() : $user->vacations())
            ->with(['users', 'dateAvailabilities', 'packingItems.checks', 'vehicles.passengers'])
            ->get();

        // De lopende reis: de verst gevorderde fase die nog niet is afgerond.
        $phaseOrder = array_flip(array_column(VacationPhase::cases(), 'value'));

        $current = $vacations
            ->reject(fn (Vacation $vacation) => $vacation->phase === VacationPhase::Finished)
            ->sortByDesc(fn (Vacation $vacation) => $phaseOrder[$vacation->phase->value])
            ->first();

        $finished = $vacations
            ->filter(fn (Vacation $vacation) => $vacation->phase === VacationPhase::Finished)
            ->sortByDesc('final_start_date')
            ->values();

        return view('dashboard', [
            'vacation' => $current,
            'finishedVacations' => $finished,
            'tasks' => $current ? $this->tasksFor($current, $request) : collect(),
            'packingProgress' => $current ? $this->packingProgress($current, $request) : null,
            'daysUntilDeparture' => $this->daysUntilDeparture($current),
        ]);
    }

    /**
     * Concrete acties voor deze gebruiker, afhankelijk van de fase. Elke taak is
     * ['label' => ..., 'hint' => ..., 'route' => ..., 'done' => bool].
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function tasksFor(Vacation $vacation, Request $request): \Illuminate\Support\Collection
    {
        $user = $request->user();
        $isParticipant = $vacation->users->contains('id', $user->id);
        $tasks = collect();

        if (! $isParticipant) {
            return $tasks;
        }

        if ($vacation->phase === VacationPhase::Planning) {
            $progress = $vacation->availabilityProgressFor($user);

            if ($progress['total'] > 0) {
                $tasks->push([
                    'label' => 'Vul je beschikbaarheid in',
                    'hint' => $progress['filled'].' van de '.$progress['total'].' dagen ingevuld',
                    'route' => route('vacations.date-planner', $vacation),
                    'done' => $progress['filled'] === $progress['total'],
                ]);
            }

            $tasks->push([
                'label' => 'Stem op skigebieden en hotels',
                'hint' => 'Laat weten wat jij een goed idee vindt',
                'route' => route('vacations.locations.index', $vacation),
                'done' => false,
            ]);
        }

        if ($vacation->phase === VacationPhase::Booking) {
            $tasks->push([
                'label' => 'Check de hotelprijzen',
                'hint' => 'De datum staat vast, dus de prijzen kloppen nu pas echt',
                'route' => route('vacations.locations.index', $vacation),
                'done' => false,
            ]);
        }

        if ($vacation->phase === VacationPhase::TravelPlanning) {
            $hasSeat = $vacation->vehicles->contains(
                fn ($vehicle) => $vehicle->passengers->contains('id', $user->id)
            );

            $tasks->push([
                'label' => $hasSeat ? 'Je hebt een plek in een auto' : 'Kies met wie je meerijdt',
                'hint' => $hasSeat ? 'Wisselen kan nog in de reisplanner' : 'Deel jezelf in bij een auto',
                'route' => route('vacations.travel-planner', $vacation),
                'done' => $hasSeat,
            ]);
        }

        $tasks->push([
            'label' => 'Werk je paklijst bij',
            'hint' => 'Vink af wat je al hebt ingepakt',
            'route' => route('vacations.packing-list', $vacation),
            'done' => false,
        ]);

        return $tasks;
    }

    /**
     * @return array{checked: int, total: int, percentage: int}|null
     */
    private function packingProgress(Vacation $vacation, Request $request): ?array
    {
        $total = $vacation->packingItems->count();

        if ($total === 0 || ! $vacation->users->contains('id', $request->user()->id)) {
            return null;
        }

        $checked = $vacation->packingItems
            ->filter(fn ($item) => $item->isCheckedBy($request->user()->id))
            ->count();

        return [
            'checked' => $checked,
            'total' => $total,
            'percentage' => (int) round($checked / $total * 100),
        ];
    }

    private function daysUntilDeparture(?Vacation $vacation): ?int
    {
        if (! $vacation?->final_start_date) {
            return null;
        }

        $days = (int) now()->startOfDay()->diffInDays($vacation->final_start_date, false);

        return $days >= 0 ? $days : null;
    }
}
