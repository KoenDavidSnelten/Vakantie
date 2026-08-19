<?php

namespace App\Http\Controllers;

use App\Enums\VacationPhase;
use App\Models\User;
use App\Models\Vacation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VacationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $vacations = $user->isAdmin()
            ? Vacation::latest()->get()
            : $user->vacations()->latest()->get();

        return view('vacations.index', [
            'vacations' => $vacations,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('vacations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $vacation = Vacation::create([
            ...$validated,
            'phase' => VacationPhase::Planning,
        ]);

        return redirect()->route('vacations.edit', $vacation)->with('status', 'vacation-created');
    }

    public function show(Request $request, Vacation $vacation): View
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        return view('vacations.show', [
            'vacation' => $vacation,
        ]);
    }

    public function edit(Request $request, Vacation $vacation): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('vacations.edit', [
            'vacation' => $vacation,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Vacation $vacation): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phase' => ['required', Rule::enum(VacationPhase::class)],
            'user_ids' => ['array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $vacation->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'phase' => $validated['phase'],
        ]);

        $vacation->users()->sync($validated['user_ids'] ?? []);

        return redirect()->route('vacations.edit', $vacation)->with('status', 'vacation-updated');
    }
}
