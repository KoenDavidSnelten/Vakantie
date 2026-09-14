<x-guest-layout title="Account maken">
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address met live-controle -->
        <div class="mt-4" x-data="emailCheck()">
            <x-input-label for="email" :value="__('Email')" />

            <div class="relative">
                <x-text-input id="email" class="block mt-1 w-full pr-9" type="email" name="email"
                                :value="old('email')" required autocomplete="username"
                                x-model="email" @input.debounce.400ms="check()" />

                <span class="pointer-events-none absolute inset-y-0 right-0 mt-1 flex items-center pr-3 text-sm">
                    <span x-show="status === 'checking'" class="text-slate-400">…</span>
                    <span x-show="status === 'available'" class="text-emerald-600 font-bold">✓</span>
                    <span x-show="status === 'invalid' || status === 'taken'" class="text-rose-600 font-bold">✗</span>
                </span>
            </div>

            <p x-show="message" x-cloak class="mt-1 text-xs"
                :class="status === 'available' ? 'text-emerald-600' : 'text-rose-600'"
                x-text="message"></p>

            <x-input-error :messages="$errors->get('email')" class="mt-2" />

            <script>
                window.emailCheck = function () {
                    return {
                        email: @js(old('email')),
                        status: 'idle',   // idle | checking | available | taken | invalid
                        message: '',
                        _controller: null,

                        async check() {
                            const value = this.email.trim();
                            if (value === '') { this.status = 'idle'; this.message = ''; return; }

                            // Simpele format-check vooraf.
                            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                                this.status = 'invalid';
                                this.message = @js(__('Dit lijkt geen geldig e-mailadres.'));
                                return;
                            }

                            this.status = 'checking';
                            this.message = '';
                            if (this._controller) this._controller.abort();
                            this._controller = new AbortController();

                            try {
                                const res = await fetch('{{ route('register.check-email') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({ email: value }),
                                    signal: this._controller.signal,
                                });

                                if (res.status === 429) { this.status = 'idle'; this.message = @js(__('Te veel pogingen, wacht even.')); return; }

                                const data = await res.json();
                                if (!data.valid) {
                                    this.status = 'invalid';
                                    this.message = @js(__('Dit lijkt geen geldig e-mailadres.'));
                                } else if (data.available) {
                                    this.status = 'available';
                                    this.message = @js(__('Dit e-mailadres is beschikbaar.'));
                                } else {
                                    this.status = 'taken';
                                    this.message = @js(__('Dit e-mailadres is al geregistreerd.'));
                                }
                            } catch (e) {
                                if (e.name === 'AbortError') return;
                                this.status = 'idle';
                                this.message = '';
                            }
                        },
                    };
                };
            </script>
        </div>

        <div x-data="passwordChecks()">
            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password"
                                x-model="password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />

                <!-- Live checklist -->
                <ul class="mt-2 space-y-1 text-xs">
                    <template x-for="rule in rules" :key="rule.label">
                        <li class="flex items-center gap-1.5"
                            :class="rule.ok ? 'text-emerald-600' : 'text-slate-400'">
                            <span x-text="rule.ok ? '✓' : '○'"></span>
                            <span x-text="rule.label"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password"
                                x-model="confirm" />

                <p x-show="confirm.length > 0" x-cloak class="mt-1 text-xs"
                    :class="match ? 'text-emerald-600' : 'text-rose-600'"
                    x-text="match ? '{{ __('Wachtwoorden komen overeen.') }}' : '{{ __('Wachtwoorden komen niet overeen.') }}'"></p>

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <script>
                window.passwordChecks = function () {
                    return {
                        password: '',
                        confirm: '',
                        get rules() {
                            const p = this.password;
                            return [
                                { label: @js(__('Minstens 10 tekens')),   ok: p.length >= 10 },
                                { label: @js(__('Een kleine letter')),    ok: /[a-z]/.test(p) },
                                { label: @js(__('Een hoofdletter')),      ok: /[A-Z]/.test(p) },
                                { label: @js(__('Een cijfer')),           ok: /[0-9]/.test(p) },
                                { label: @js(__('Een symbool')),          ok: /[^A-Za-z0-9]/.test(p) },
                            ];
                        },
                        get match() {
                            return this.confirm.length > 0 && this.password === this.confirm;
                        },
                    };
                };
            </script>
        </div>

        @if ($registrationCodeRequired ?? false)
            <!-- Registratiecode (anti-spam) met live-controle -->
            <div class="mt-4" x-data="registrationCodeCheck()">
                <x-input-label for="registration_code" :value="__('Registratiecode')" />

                <div class="relative">
                    <x-text-input id="registration_code" class="block mt-1 w-full pr-9"
                                    type="password"
                                    name="registration_code" required autocomplete="off"
                                    x-model="code"
                                    @input.debounce.400ms="check()" />

                    <span class="pointer-events-none absolute inset-y-0 right-0 mt-1 flex items-center pr-3 text-sm">
                        <span x-show="status === 'checking'" class="text-slate-400">…</span>
                        <span x-show="status === 'valid'" class="text-emerald-600 font-bold">✓</span>
                        <span x-show="status === 'invalid'" class="text-rose-600 font-bold">✗</span>
                    </span>
                </div>

                <p class="mt-1 text-xs" :class="{
                        'text-slate-500': status !== 'valid' && status !== 'invalid',
                        'text-emerald-600': status === 'valid',
                        'text-rose-600': status === 'invalid',
                    }"
                    x-text="message">{{ __('Vraag deze code aan de beheerder.') }}</p>

                <x-input-error :messages="$errors->get('registration_code')" class="mt-2" />
            </div>

            <script>
                window.registrationCodeCheck = function () {
                    return {
                        code: '{{ old('registration_code') }}',
                        status: 'idle',   // idle | checking | valid | invalid
                        message: @js(__('Vraag deze code aan de beheerder.')),
                        _controller: null,

                        async check() {
                            const value = this.code.trim();
                            if (value === '') {
                                this.status = 'idle';
                                this.message = @js(__('Vraag deze code aan de beheerder.'));
                                return;
                            }

                            this.status = 'checking';
                            this.message = @js(__('Code controleren…'));

                            // Vorige lopende check afbreken.
                            if (this._controller) this._controller.abort();
                            this._controller = new AbortController();

                            try {
                                const res = await fetch('{{ route('register.check-code') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({ registration_code: value }),
                                    signal: this._controller.signal,
                                });

                                if (res.status === 429) {
                                    this.status = 'idle';
                                    this.message = @js(__('Te veel pogingen, wacht even.'));
                                    return;
                                }

                                const data = await res.json();
                                if (data.valid) {
                                    this.status = 'valid';
                                    this.message = @js(__('Code klopt!'));
                                } else {
                                    this.status = 'invalid';
                                    this.message = @js(__('Deze code klopt niet.'));
                                }
                            } catch (e) {
                                if (e.name === 'AbortError') return;
                                this.status = 'idle';
                                this.message = @js(__('Kon de code nu niet controleren.'));
                            }
                        },
                    };
                };
            </script>
        @endif

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
