<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'registrationCodeRequired' => filled(config('auth.registration_code')),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        $expectedCode = config('auth.registration_code');
        if (filled($expectedCode)) {
            $rules['registration_code'] = ['required', 'string'];
        }

        $request->validate($rules);

        // Timing-veilige controle van de gedeelde registratiecode (anti-spam).
        if (filled($expectedCode)
            && ! hash_equals((string) $expectedCode, (string) $request->input('registration_code'))) {
            throw ValidationException::withMessages([
                'registration_code' => __('De registratiecode klopt niet.'),
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Live-check of de ingevulde registratiecode klopt (voor directe feedback
     * op het formulier). Rate-limited via de route tegen bruteforce.
     */
    public function checkCode(Request $request): JsonResponse
    {
        $expectedCode = config('auth.registration_code');

        // Geen code ingesteld? Dan is er niets te controleren.
        if (blank($expectedCode)) {
            return response()->json(['required' => false, 'valid' => true]);
        }

        $valid = hash_equals((string) $expectedCode, (string) $request->input('registration_code'));

        return response()->json(['required' => true, 'valid' => $valid]);
    }

    /**
     * Live-check of een e-mailadres geldig is en nog niet in gebruik is, voor
     * directe feedback op het formulier. Rate-limited via de route.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = trim((string) $request->input('email'));

        $valid = strlen($email) <= 255 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        if (! $valid) {
            return response()->json(['valid' => false, 'available' => false]);
        }

        $taken = User::where('email', mb_strtolower($email))->exists();

        return response()->json(['valid' => true, 'available' => ! $taken]);
    }
}
