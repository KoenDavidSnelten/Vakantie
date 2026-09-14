<?php

namespace App\Http\Controllers;

use App\Enums\AvailabilityStatus;
use App\Enums\VacationPhase;
use App\Models\Vacation;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VacationDatePlannerController extends Controller
{
    /**
     * Sentinelwaarde uit het formulier waarmee een dag weer leeg wordt gemaakt.
     */
    public const CLEAR_STATUS = 'clear';

    public function show(Request $request, Vacation $vacation): View|RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        if (! $vacation->phase->hasPlannerAccess()) {
            return redirect()->route('vacations.show', $vacation);
        }

        $vacation->load(['users', 'dateAvailabilities']);

        return view('vacations.date-planner', [
            'vacation' => $vacation,
        ]);
    }

    public function updateRange(Request $request, Vacation $vacation): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'planning_start_date' => ['required', 'date'],
            'planning_end_date' => ['required', 'date', 'after_or_equal:planning_start_date'],
        ]);

        $vacation->update($validated);

        $validDates = collect(CarbonPeriod::create($validated['planning_start_date'], $validated['planning_end_date']))
            ->map(fn ($date) => $date->format('Y-m-d'));

        $vacation->dateAvailabilities()->whereNotIn('date', $validDates)->delete();

        return redirect()->route('vacations.date-planner', $vacation)->with('status', 'date-range-updated');
    }

    public function upsertAvailability(Request $request, Vacation $vacation): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        abort_unless($vacation->users->contains('id', $user->id), 403);
        abort_if($vacation->final_start_date && $vacation->final_end_date, 403);

        $validDates = $vacation->planningDates();

        abort_if(empty($validDates), 404);

        // 'clear' is geen echte status maar de weg terug: daarmee haal je een
        // per ongeluk ingevulde dag weer helemaal leeg.
        $allowed = array_merge(array_column(AvailabilityStatus::cases(), 'value'), [self::CLEAR_STATUS]);

        $validated = $request->validate([
            'dates' => ['required', 'array'],
            'dates.*' => ['required', Rule::in($allowed)],
        ]);

        $toClear = [];

        foreach ($validDates as $date) {
            if (! array_key_exists($date, $validated['dates'])) {
                continue;
            }

            if ($validated['dates'][$date] === self::CLEAR_STATUS) {
                $toClear[] = $date;

                continue;
            }

            $vacation->dateAvailabilities()->updateOrCreate(
                ['user_id' => $user->id, 'date' => $date],
                ['status' => $validated['dates'][$date]],
            );
        }

        if (! empty($toClear)) {
            $vacation->dateAvailabilities()
                ->where('user_id', $user->id)
                ->whereIn('date', $toClear)
                ->delete();
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'availability-updated']);
        }

        return redirect()->route('vacations.date-planner', $vacation)->with('status', 'availability-updated');
    }

    public function finalize(Request $request, Vacation $vacation): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'final_start_date' => ['required', 'date'],
            'final_end_date' => ['required', 'date', 'after_or_equal:final_start_date'],
        ]);

        // Choosing the final date ends the plan phase: availability voting
        // locks, and hotel booking becomes available. Only bumps forward
        // re-picking the date later (e.g. from Booking) won't move it back.
        if ($vacation->phase === VacationPhase::Planning) {
            $validated['phase'] = VacationPhase::Booking;
        }

        $vacation->update($validated);

        return redirect()->route('vacations.date-planner', $vacation)->with('status', 'final-date-set');
    }
}
