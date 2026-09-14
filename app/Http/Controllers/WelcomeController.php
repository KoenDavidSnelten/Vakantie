<?php

namespace App\Http\Controllers;

use App\Enums\VacationPhase;
use App\Models\Vacation;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    /**
     * De openbare voorpagina. Toont de eerstvolgende reis en de afgeronde reizen
     * uit de database, zodat de tekst niet meer met de hand hoeft te worden
     * bijgewerkt. Bewust alleen naam, fase en datum, verder niets.
     */
    public function __invoke(): View
    {
        $vacations = Vacation::query()
            ->orderByRaw('final_start_date is null')
            ->orderBy('final_start_date')
            ->get();

        $phaseOrder = array_flip(array_column(VacationPhase::cases(), 'value'));

        return view('welcome', [
            'nextVacation' => $vacations
                ->reject(fn (Vacation $vacation) => $vacation->phase === VacationPhase::Finished)
                ->sortByDesc(fn (Vacation $vacation) => $phaseOrder[$vacation->phase->value])
                ->first(),
            'pastVacations' => $vacations
                ->filter(fn (Vacation $vacation) => $vacation->phase === VacationPhase::Finished)
                ->sortByDesc('final_start_date')
                ->values(),
        ]);
    }
}
