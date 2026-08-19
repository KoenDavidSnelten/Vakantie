<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationTravelPlannerController extends Controller
{
    public function show(Request $request, Vacation $vacation): View|RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        if (! $vacation->phase->hasPlannerAccess()) {
            return redirect()->route('vacations.show', $vacation);
        }

        $vacation->load(['users', 'vehicles.addedBy', 'travelOptions.addedBy']);

        return view('vacations.travel-planner', [
            'vacation' => $vacation,
        ]);
    }
}
