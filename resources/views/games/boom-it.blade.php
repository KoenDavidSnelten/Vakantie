@php
    $questions = [
        'Wie wordt het snelst miljonair?',
        'Wie zou er drugs verkopen?',
        'Wie belandt er ooit in de gevangenis?',
        'Wie stuurt de meeste dronken appjes?',
        'Wie zou er naakt gaan zwemmen?',
        'Wie wordt er als eerste dronken vanavond?',
        'Wie heeft de gênantste zoekgeschiedenis?',
        'Wie zou z\'n ex terugbellen na 3 biertjes?',
        'Wie wordt later een BN\'er?',
        'Wie kan het slechtst tegen z\'n drank?',
        'Wie zou er stiekem vals spelen bij een spelletje?',
        'Wie heeft de meeste geheime tinderdates gehad?',
        'Wie zou er flauwvallen bij het zien van bloed?',
        'Wie verslaapt zich morgen gegarandeerd?',
        'Wie zou als eerste een gevecht beginnen?',
        'Wie heeft de gekste bijnaam verdiend?',
        'Wie zou er meedoen aan een realityshow?',
        'Wie kan het langst niet op z\'n telefoon kijken?',
        'Wie heeft het meeste geld uitgegeven aan onzin?',
        'Wie zou er zonder navigatie verdwalen in eigen stad?',
        'Wie wordt de strengste ouder later?',
        'Wie zou er per ongeluk de groepsapp verkeerd gebruiken?',
        'Wie durft de bar op te gaan om te dansen?',
        'Wie heeft de meeste smoesjes om onder afspraken uit te komen?',
        'Wie zou er huilen bij een romantische film?',
        'Wie is het slechtst in geheimen bewaren?',
        'Wie zou er gratis eten scoren met een praatje?',
        'Wie heeft de meeste matches maar de minste dates?',
        'Wie zou er spontaan een tattoo laten zetten?',
        'Wie is de grootste roddeltante van de groep?',
        'Wie zou er te laat komen op z\'n eigen bruiloft?',
        'Wie heeft de wildste vakantieverhalen?',
        'Wie zou er in slaap vallen op een feestje?',
        'Wie geeft het meeste geld uit aan afhaaleten?',
        'Wie zou er beroemd worden op TikTok?',
        'Wie heeft de meeste exen?',
        'Wie zou er zonder blikken of blozen liegen?',
        'Wie kan het beste plassen zonder handen te wassen (geef toe)?',
        'Wie zou er het langst overleven op een onbewoond eiland?',
        'Wie is stiekem de grootste player van de groep?',

        // ---- Meer "Wie..." vragen ----
        'Wie zou het langst z\'n mond kunnen houden?',
        'Wie is het vaakst verliefd?',
        'Wie geeft het meeste geld uit aan kleren?',
        'Wie zou een goede spion zijn?',
        'Wie kan het slechtst dansen?',
        'Wie is het meest chaotisch?',
        'Wie eet altijd het meest?',
        'Wie is het vaakst te laat?',
        'Wie zou ooit een eigen bedrijf beginnen?',
        'Wie kan het beste zingen?',
        'Wie is het meest lui?',
        'Wie durft het meest?',
        'Wie liegt het vaakst?',
        'Wie zou het langst wakker blijven op een feest?',
        'Wie heeft de meeste selfies op z\'n telefoon?',
        'Wie kan het slechtst koken?',
        'Wie checkt het vaakst z\'n telefoon?',
        'Wie zou het snelst een boete krijgen?',
        'Wie is de grootste stiekemerd?',
        'Wie zou als laatste opstaan \'s ochtends?',
        'Wie geeft het beste advies?',
        'Wie is het meest jaloers?',
        'Wie zou het langst zonder eten kunnen?',
        'Wie neemt de gekste beslissingen?',
        'Wie is het meest verlegen?',

        // ---- "Noem 3..." opnoem-opdrachten (snel opnoemen en doorgeven) ----
        'Noem 3 landen die met een A beginnen.',
        'Noem 3 landen die met een B beginnen.',
        'Noem 3 automerken.',
        'Noem 3 biermerken.',
        'Noem 3 voetbalclubs.',
        'Noem 3 kleuren.',
        'Noem 3 dingen in een keuken.',
        'Noem 3 wintersporten.',
        'Noem 3 Nederlandse steden.',
        'Noem 3 dieren met vier poten.',
        'Noem 3 pizzasmaken.',
        'Noem 3 cocktails.',
        'Noem 3 films.',
        'Noem 3 zangers of bands.',
        'Noem 3 dingen die je meeneemt op vakantie.',
        'Noem 3 fruitsoorten.',
        'Noem 3 beroepen.',
        'Noem 3 dingen die rood zijn.',
        'Noem 3 kledingmerken.',
        'Noem 3 Disney-figuren.',
        'Noem 3 dingen die je in de supermarkt koopt.',
        'Noem 3 sporten zonder bal.',
        'Noem 3 social media apps.',
        'Noem 3 dingen die kunnen vliegen.',
        'Noem 3 jongensnamen die met een J beginnen.',
        'Noem 3 meisjesnamen die met een M beginnen.',
        'Noem 3 dieren die met een K beginnen.',
        'Noem 3 dingen die je op een festival ziet.',
        'Noem 3 wereldsteden.',
        'Noem 3 groentesoorten.',
        'Noem 3 dingen die je in een badkamer vindt.',
        'Noem 3 superhelden.',
        'Noem 3 talen.',
        'Noem 3 dingen die kraken.',
        'Noem 2 dingen die je nooit uitleent.',
        'Noem 3 emoji die je vaak gebruikt.',
        'Noem 3 dingen waar je bang voor bent.',
        'Noem 3 merken frisdrank.',
        'Noem 3 dingen die je aan het strand doet.',
        'Noem 3 wintersportmerken of ski-merken.',
    ];

    $difficulties = [
        'easy'    => ['label' => 'Easy',    'emoji' => '😊', 'min' => 1, 'max' => 4,  'shotChance' => 0,    'desc' => '1 tot 4 slokken', 'ring' => 'ring-emerald-200', 'accent' => 'text-emerald-600', 'bar' => 'bg-emerald-500'],
        'medium'  => ['label' => 'Medium',  'emoji' => '😏', 'min' => 4, 'max' => 8,  'shotChance' => 0,    'desc' => '4 tot 8 slokken', 'ring' => 'ring-amber-200',   'accent' => 'text-amber-600',   'bar' => 'bg-amber-500'],
        'extreme' => ['label' => 'Extreme', 'emoji' => '🔥', 'min' => 8, 'max' => 12, 'shotChance' => 0.25, 'desc' => '8 tot 12 slokken of een shot 🥃', 'ring' => 'ring-rose-200', 'accent' => 'text-rose-600', 'bar' => 'bg-rose-500'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                💥 {{ __('Boom It') }}
            </h2>
            <a href="{{ route('games.index') }}" class="text-sm text-slate-600 hover:text-slate-900">
                &larr; {{ __('Spellen') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="boomGame(@js(array_values($questions)), @js($difficulties))">
        {{-- ---------- Setup ---------- --}}
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-100 rounded-2xl">
                <h3 class="text-lg font-semibold text-slate-900">Hoe werkt het?</h3>
                <ol class="mt-3 space-y-1.5 text-sm text-slate-600 list-decimal list-inside">
                    <li>Er verschijnt een vraag, bijv. <em>"Wie wordt het snelst miljonair?"</em></li>
                    <li>De bom tikt door, geef de telefoon door aan de persoon die het beste past.</li>
                    <li>Die persoon <strong>tikt op het scherm</strong> voor de volgende vraag en geeft weer door.</li>
                    <li>Zo gaat de telefoon rond tot de bom na een geheime tijd (10–30 sec) ontploft. 💥</li>
                    <li>Wie 'm dan vasthoudt, moet drinken!</li>
                </ol>

                <p class="mt-6 text-sm font-semibold text-slate-900">Kies een niveau om te starten:</p>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach ($difficulties as $key => $d)
                        <button type="button"
                            @click="startGame('{{ $key }}')"
                            class="text-left rounded-2xl p-4 ring-2 {{ $d['ring'] }} bg-white hover:shadow-md hover:-translate-y-0.5 transition focus:outline-none focus:ring-4">
                            <p class="text-2xl">{{ $d['emoji'] }}</p>
                            <p class="mt-2 text-base font-bold {{ $d['accent'] }}">{{ $d['label'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $d['desc'] }}</p>
                        </button>
                    @endforeach
                </div>

                <p class="mt-6 text-xs text-slate-400">
                    Tip: zet je geluid aan 🔊 en houd de telefoon goed vast, hij trilt en tikt steeds sneller.
                </p>
            </div>
        </div>

        {{-- ---------- Immersive game overlay (ticking + boom) ---------- --}}
        <div
            x-show="state !== 'setup'"
            x-cloak
            class="fixed inset-0 z-[60] flex flex-col items-center justify-between p-6 text-center select-none"
            :style="bgStyle"
        >
            {{-- Ticking, tap anywhere to pass on to the next question (timer keeps running) --}}
            <template x-if="state === 'ticking'">
                <div class="flex flex-1 flex-col items-center justify-between w-full max-w-lg mx-auto cursor-pointer"
                     @click="advance()" role="button" aria-label="Volgende vraag">
                    <div class="pt-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/60">Snel! 💣</p>
                        <p class="mt-3 text-2xl sm:text-3xl font-extrabold text-white leading-snug" x-text="question"></p>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="text-8xl sm:text-9xl" :style="bombStyle">💣</div>
                        <p class="mt-6 text-lg font-semibold text-white/90 animate-pulse">Doorgegeven? Tik voor de volgende 👆</p>
                    </div>

                    <button type="button" @click.stop="stop()"
                        class="mb-2 text-sm text-white/50 hover:text-white/90 underline underline-offset-4">
                        Stoppen
                    </button>
                </div>
            </template>

            {{-- Boom --}}
            <template x-if="state === 'boom'">
                <div class="flex flex-1 flex-col items-center justify-center w-full max-w-lg mx-auto">
                    <div class="text-8xl sm:text-9xl animate-bounce">💥</div>
                    <p class="mt-4 text-4xl sm:text-5xl font-black text-white tracking-tight">BOOM!</p>
                    <p class="mt-6 text-lg text-white/80">Jij hield 'm vast…</p>
                    <p class="mt-2 text-3xl sm:text-4xl font-extrabold text-white">
                        Neem <span class="text-amber-300" x-text="resultText"></span>
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <button type="button" @click="nextRound()"
                            class="rounded-full bg-white px-8 py-3 text-base font-bold text-slate-900 hover:bg-slate-100 transition">
                            Volgende ronde →
                        </button>
                        <button type="button" @click="stop()"
                            class="rounded-full bg-white/10 px-8 py-3 text-base font-semibold text-white ring-1 ring-white/40 hover:bg-white/20 transition">
                            Stoppen
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        window.boomGame = function (questions, difficulties) {
            return {
                questions: questions,
                difficulties: difficulties,
                state: 'setup',          // 'setup' | 'ticking' | 'boom'
                difficulty: 'medium',
                question: '',
                result: null,            // { type: 'sips'|'shot', amount }
                flashOn: false,
                progress: 0,             // 0..1 (never shown as a number to players)

                // internals
                _order: [],
                _orderIndex: 0,
                _raf: null,
                _startTime: 0,
                _duration: 0,
                _nextTick: 0,
                _audio: null,
                _wakeLock: null,

                // ---------- game flow ----------
                startGame(level) {
                    this.difficulty = level;
                    this._ensureAudio();
                    this._requestWakeLock();
                    this._shuffle();
                    this.startRound();
                },

                startRound() {
                    this._nextQuestion();
                    this.result = null;
                    this.progress = 0;
                    this.flashOn = false;
                    this._duration = (10 + Math.random() * 20) * 1000; // 10–30s, hidden
                    this._startTime = performance.now();
                    this._nextTick = 0;
                    this.state = 'ticking';
                    this._loop();
                },

                nextRound() {
                    this.startRound();
                },

                // Passed the phone on: show the next question. The fuse keeps burning.
                advance() {
                    if (this.state !== 'ticking') return;
                    this._nextQuestion();
                    this._beep(1040, 0.05);
                    if (navigator.vibrate) navigator.vibrate(15);
                },

                stop() {
                    cancelAnimationFrame(this._raf);
                    this.flashOn = false;
                    this.state = 'setup';
                    this._releaseWakeLock();
                },

                // ---------- ticking loop ----------
                _loop() {
                    const elapsed = performance.now() - this._startTime;
                    this.progress = Math.min(elapsed / this._duration, 1);

                    if (elapsed >= this._nextTick) {
                        this._tick();
                        // interval shrinks from ~620ms down to ~120ms as tension builds
                        const interval = 620 - this.progress * 500;
                        this._nextTick = elapsed + interval;
                    }

                    if (this.progress >= 1) {
                        this._explode();
                        return;
                    }
                    this._raf = requestAnimationFrame(() => this._loop());
                },

                _tick() {
                    this.flashOn = !this.flashOn;
                    this._beep(760 + this.progress * 520, 0.045);
                    if (navigator.vibrate) navigator.vibrate(25);
                },

                _explode() {
                    cancelAnimationFrame(this._raf);
                    this.flashOn = false;
                    this.progress = 1;
                    this.result = this._rollResult();
                    this.state = 'boom';
                    this._explosionSound();
                    if (navigator.vibrate) navigator.vibrate([140, 70, 260]);
                },

                _rollResult() {
                    const d = this.difficulties[this.difficulty];
                    if (d.shotChance && Math.random() < d.shotChance) {
                        return { type: 'shot' };
                    }
                    const span = d.max - d.min;
                    return { type: 'sips', amount: d.min + Math.floor(Math.random() * (span + 1)) };
                },

                // ---------- questions ----------
                _shuffle() {
                    this._order = this.questions.map((_, i) => i);
                    for (let i = this._order.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [this._order[i], this._order[j]] = [this._order[j], this._order[i]];
                    }
                    this._orderIndex = 0;
                },

                _nextQuestion() {
                    if (this._orderIndex >= this._order.length) this._shuffle();
                    this.question = this.questions[this._order[this._orderIndex]];
                    this._orderIndex++;
                },

                // ---------- display ----------
                get bgStyle() {
                    if (this.state === 'boom') {
                        return 'background-color: rgb(190, 18, 60);'; // rose-700
                    }
                    if (this.flashOn) {
                        const r = 150 + Math.round(this.progress * 90);
                        return `background-color: rgb(${r}, 24, 28);`;
                    }
                    return 'background-color: rgb(15, 18, 26);'; // near-black slate
                },

                get bombStyle() {
                    const scale = 1 + this.progress * 0.5;
                    const wobble = this.progress * 4;
                    const angle = this.flashOn ? wobble : -wobble;
                    return `transform: scale(${scale.toFixed(2)}) rotate(${angle.toFixed(1)}deg); transition: transform 90ms ease-out;`;
                },

                get resultText() {
                    if (!this.result) return '';
                    if (this.result.type === 'shot') return 'een shot 🥃';
                    return this.result.amount + ' ' + (this.result.amount === 1 ? 'slok' : 'slokken');
                },

                // ---------- audio (Web Audio, no files) ----------
                _ensureAudio() {
                    if (!this._audio) {
                        const AC = window.AudioContext || window.webkitAudioContext;
                        if (AC) this._audio = new AC();
                    }
                    if (this._audio && this._audio.state === 'suspended') this._audio.resume();
                },

                _beep(freq, dur) {
                    if (!this._audio) return;
                    const ctx = this._audio;
                    const t = ctx.currentTime;
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'square';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.0001, t);
                    gain.gain.exponentialRampToValueAtTime(0.22, t + 0.005);
                    gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);
                    osc.connect(gain).connect(ctx.destination);
                    osc.start(t);
                    osc.stop(t + dur + 0.02);
                },

                _explosionSound() {
                    if (!this._audio) return;
                    const ctx = this._audio;
                    const t = ctx.currentTime;
                    const dur = 1.1;

                    // noise burst through a sweeping low-pass
                    const buffer = ctx.createBuffer(1, Math.floor(ctx.sampleRate * dur), ctx.sampleRate);
                    const data = buffer.getChannelData(0);
                    for (let i = 0; i < data.length; i++) {
                        data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / data.length, 2);
                    }
                    const noise = ctx.createBufferSource();
                    noise.buffer = buffer;
                    const filter = ctx.createBiquadFilter();
                    filter.type = 'lowpass';
                    filter.frequency.setValueAtTime(1400, t);
                    filter.frequency.exponentialRampToValueAtTime(120, t + dur);
                    const ng = ctx.createGain();
                    ng.gain.setValueAtTime(0.9, t);
                    ng.gain.exponentialRampToValueAtTime(0.001, t + dur);
                    noise.connect(filter).connect(ng).connect(ctx.destination);
                    noise.start(t);

                    // deep boom
                    const osc = ctx.createOscillator();
                    const og = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(140, t);
                    osc.frequency.exponentialRampToValueAtTime(38, t + 0.6);
                    og.gain.setValueAtTime(0.8, t);
                    og.gain.exponentialRampToValueAtTime(0.001, t + 0.7);
                    osc.connect(og).connect(ctx.destination);
                    osc.start(t);
                    osc.stop(t + 0.75);
                },

                // ---------- keep screen awake while playing ----------
                async _requestWakeLock() {
                    try {
                        if ('wakeLock' in navigator) {
                            this._wakeLock = await navigator.wakeLock.request('screen');
                        }
                    } catch (e) { /* not critical */ }
                },
                _releaseWakeLock() {
                    try {
                        if (this._wakeLock) { this._wakeLock.release(); this._wakeLock = null; }
                    } catch (e) { /* ignore */ }
                },
            };
        };
    </script>
</x-app-layout>
