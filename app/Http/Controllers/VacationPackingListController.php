<?php

namespace App\Http\Controllers;

use App\Enums\PackingCategory;
use App\Models\Vacation;
use App\Models\VacationPackingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VacationPackingListController extends Controller
{
    public function index(Request $request, Vacation $vacation): View
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        if ($vacation->packingItems()->doesntExist()) {
            $this->seedDefaultItems($vacation);
        }

        $vacation->load(['users', 'packingItems.checks']);

        return view('vacations.packing-list', [
            'vacation' => $vacation,
        ]);
    }

    public function store(Request $request, Vacation $vacation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        $validated = $request->validateWithBag('packingItem', [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(PackingCategory::class)],
        ]);

        $vacation->packingItems()->create([
            'user_id' => $user->id,
            'category' => $validated['category'],
            'name' => $validated['name'],
        ]);

        return redirect()->route('vacations.packing-list', $vacation)->with('status', 'packing-item-added');
    }

    public function destroy(Request $request, Vacation $vacation, VacationPackingItem $item): RedirectResponse
    {
        $user = $request->user();

        abort_unless($item->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $item->user_id === $user->id, 403);

        $item->delete();

        return redirect()->route('vacations.packing-list', $vacation)->with('status', 'packing-item-removed');
    }

    public function toggle(Request $request, Vacation $vacation, VacationPackingItem $item): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        abort_unless($item->vacation_id === $vacation->id, 404);
        abort_unless($vacation->users->contains('id', $user->id), 403);

        $check = $item->checks()->where('user_id', $user->id)->first();

        if ($check) {
            $check->delete();
        } else {
            $item->checks()->create(['user_id' => $user->id]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'packing-item-toggled']);
        }

        return redirect()->route('vacations.packing-list', $vacation)->with('status', 'packing-item-toggled');
    }

    /**
     * A sensible starting checklist for a ski trip, so the list isn't empty
     * the first time anyone opens it. People can still add or remove items
     * freely afterwards.
     */
    private function seedDefaultItems(Vacation $vacation): void
    {
        $defaults = [
            PackingCategory::WinterSportClothing->value => [
                'Ski- of snowboardplank', 'Skischoenen', 'Helm', 'Skibril',
                'Handschoenen', 'Skijas', 'Skibroek', 'Thermo-onderkleding',
                'Skisokken', 'Muts of buff',
            ],
            PackingCategory::Toiletries->value => [
                'Tandenborstel', 'Tandpasta', 'Shampoo', 'Zonnebrandcrème', 'Lippenbalsem', 'Deodorant',
            ],
            PackingCategory::RegularClothing->value => [
                'Trui', 'Broek', 'Ondergoed', 'Sokken', 'Pyjama', 'Regenjas',
            ],
        ];

        foreach ($defaults as $category => $items) {
            foreach ($items as $name) {
                $vacation->packingItems()->create([
                    'user_id' => null,
                    'category' => $category,
                    'name' => $name,
                ]);
            }
        }
    }
}
