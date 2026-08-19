<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\VacationSkiArea;
use App\Models\VacationSkiAreaComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VacationSkiAreaCommentController extends Controller
{
    public function store(Request $request, Vacation $vacation, VacationSkiArea $skiArea): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($user->isAdmin() || $vacation->users->contains('id', $user->id), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $skiArea->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'comment-added');
    }

    public function destroy(Request $request, Vacation $vacation, VacationSkiArea $skiArea, VacationSkiAreaComment $comment): RedirectResponse
    {
        $user = $request->user();

        abort_unless($skiArea->vacation_id === $vacation->id, 404);
        abort_unless($comment->vacation_ski_area_id === $skiArea->id, 404);
        abort_unless($user->isAdmin() || $comment->user_id === $user->id, 403);

        $comment->delete();

        return redirect()->route('vacations.locations.index', $vacation)->with('status', 'comment-removed');
    }
}
