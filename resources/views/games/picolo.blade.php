@php
    // Kaarttypes met kleuren/labels. De class-namen staan hier letterlijk zodat
    // Tailwind ze meeneemt in de build (ook al gebruiken we ze via JS).
    $types = [
        'direct'    => ['label' => 'Drinken',   'emoji' => '🍺', 'badge' => 'bg-sky-100 text-sky-700',       'bar' => 'bg-sky-500'],
        'group'     => ['label' => 'Groep',     'emoji' => '👥', 'badge' => 'bg-violet-100 text-violet-700', 'bar' => 'bg-violet-500'],
        'challenge' => ['label' => 'Challenge',  'emoji' => '🎯', 'badge' => 'bg-amber-100 text-amber-700',   'bar' => 'bg-amber-500'],
        'vote'      => ['label' => 'Stemronde',  'emoji' => '🗳️', 'badge' => 'bg-rose-100 text-rose-700',     'bar' => 'bg-rose-500'],
        'rule'      => ['label' => 'Regel',      'emoji' => '📜', 'badge' => 'bg-emerald-100 text-emerald-700','bar' => 'bg-emerald-500'],
        'endrule'   => ['label' => 'Regel voorbij','emoji' => '🗑️', 'badge' => 'bg-slate-200 text-slate-600',  'bar' => 'bg-slate-400'],
        'duel'      => ['label' => 'Duel',       'emoji' => '⚔️', 'badge' => 'bg-indigo-100 text-indigo-700', 'bar' => 'bg-indigo-500'],
    ];

    // Kaartjes. need = aantal verschillende spelers dat nodig is ({p1},{p2},{p3}).
    // {sips} wordt vervangen door een willekeurig getal (2–5).
    $cards = [
        // ---------- Gezellig ----------
        ['type' => 'direct',    'level' => 'gezellig', 'need' => 1, 'text' => '{p1}, neem {sips} slokken.'],
        ['type' => 'direct',    'level' => 'gezellig', 'need' => 1, 'text' => '{p1} mag {sips} slokken uitdelen over de groep.'],
        ['type' => 'direct',    'level' => 'gezellig', 'need' => 2, 'text' => '{p1} en {p2} klinken en drinken allebei {sips} slokken.'],
        ['type' => 'group',     'level' => 'gezellig', 'need' => 0, 'text' => 'Iedereen die vandaag koffie heeft gedronken, neemt 2 slokken.'],
        ['type' => 'group',     'level' => 'gezellig', 'need' => 0, 'text' => 'De jongste van de groep deelt 3 slokken uit.'],
        ['type' => 'group',     'level' => 'gezellig', 'need' => 0, 'text' => 'Iedereen die een relatie heeft, drinkt 2 slokken.'],
        ['type' => 'group',     'level' => 'gezellig', 'need' => 0, 'text' => 'Laatste die zijn duim op tafel legt, neemt 3 slokken.'],
        ['type' => 'group',     'level' => 'gezellig', 'need' => 0, 'text' => 'Iedereen met bruine ogen proost en drinkt 2 slokken.'],
        ['type' => 'challenge', 'level' => 'gezellig', 'need' => 1, 'text' => '{p1}, doe je beste dansmove van 10 sec, of neem {sips} slokken.'],
        ['type' => 'challenge', 'level' => 'gezellig', 'need' => 1, 'text' => '{p1}, noem 5 automerken binnen 10 sec, anders {sips} slokken.'],
        ['type' => 'challenge', 'level' => 'gezellig', 'need' => 1, 'text' => '{p1}, praat 30 sec zonder je lippen op elkaar te doen, of drink {sips} slokken.'],
        ['type' => 'vote',      'level' => 'gezellig', 'need' => 0, 'text' => 'Wie is het grappigst? Tel af 3-2-1 en wijs. Meeste stemmen neemt {sips} slokken.'],
        ['type' => 'vote',      'level' => 'gezellig', 'need' => 0, 'text' => 'Wie komt altijd te laat? Stem, de "winnaar" drinkt {sips} slokken.'],
        ['type' => 'rule',      'level' => 'gezellig', 'need' => 0, 'text' => 'Nieuwe regel: niemand mag meer namen zeggen. Overtreding = 2 slokken. (rest van het spel)'],
        ['type' => 'rule',      'level' => 'gezellig', 'need' => 0, 'text' => 'Nieuwe regel: drinken doe je met je linkerhand. Vergeten = 2 slokken.'],
        ['type' => 'duel',      'level' => 'gezellig', 'need' => 2, 'text' => '{p1} vs {p2}: duimworstelen. Verliezer neemt {sips} slokken.'],
        ['type' => 'duel',      'level' => 'gezellig', 'need' => 2, 'text' => '{p1} en {p2}: wie de ander het eerst aan het lachen maakt wint. Verliezer drinkt {sips} slokken.'],

        // ---------- Pittig ----------
        ['type' => 'direct',    'level' => 'pittig', 'need' => 1, 'text' => '{p1}, neem {sips} stevige slokken.'],
        ['type' => 'direct',    'level' => 'pittig', 'need' => 2, 'text' => '{p1} kiest {p2} uit om samen {sips} slokken te drinken.'],
        ['type' => 'group',     'level' => 'pittig', 'need' => 0, 'text' => 'Iedereen die weleens dronken heeft geappt met een ex, drinkt 3 slokken.'],
        ['type' => 'group',     'level' => 'pittig', 'need' => 0, 'text' => 'Iedereen die weleens is blijven slapen na een eerste date, neemt 3 slokken.'],
        ['type' => 'group',     'level' => 'pittig', 'need' => 0, 'text' => 'Iedereen die deze week iets deed waar hij spijt van had, drinkt 2 slokken.'],
        ['type' => 'challenge', 'level' => 'pittig', 'need' => 1, 'text' => '{p1}, laat je laatste foto in je camera-rol zien, of neem {sips} slokken.'],
        ['type' => 'challenge', 'level' => 'pittig', 'need' => 1, 'text' => '{p1}, praat de rest van de ronde in een raar accent, anders {sips} slokken.'],
        ['type' => 'challenge', 'level' => 'pittig', 'need' => 2, 'text' => '{p1}, geef {p2} een oprecht compliment, of jullie drinken allebei {sips} slokken.'],
        ['type' => 'vote',      'level' => 'pittig', 'need' => 0, 'text' => 'Wie zou het snelst flirten met een onbekende? Stem, die persoon drinkt {sips} slokken.'],
        ['type' => 'vote',      'level' => 'pittig', 'need' => 0, 'text' => 'Wie is de grootste player? Stem, meeste stemmen neemt {sips} slokken.'],
        ['type' => 'rule',      'level' => 'pittig', 'need' => 0, 'text' => 'Nieuwe regel: je mag niet meer "nee" zeggen. Overtreding = 3 slokken.'],
        ['type' => 'rule',      'level' => 'pittig', 'need' => 1, 'text' => 'Nieuwe regel: {p1} is de baas. Als {p1} drinkt, drinkt iedereen mee. (3 rondes)'],
        ['type' => 'duel',      'level' => 'pittig', 'need' => 2, 'text' => '{p1} vs {p2}: staarwedstrijd. Wie het eerst knippert drinkt {sips} slokken.'],
        ['type' => 'duel',      'level' => 'pittig', 'need' => 2, 'text' => '{p1} vs {p2}: steen-papier-schaar, best of 3. Verliezer drinkt {sips} slokken.'],

        // ---------- Extreem ----------
        ['type' => 'direct',    'level' => 'extreem', 'need' => 1, 'text' => '{p1}, neem een shot. 🥃'],
        ['type' => 'direct',    'level' => 'extreem', 'need' => 2, 'text' => '{p1} en {p2}, neem samen een shot.'],
        ['type' => 'group',     'level' => 'extreem', 'need' => 1, 'text' => 'Nooit-heb-ik-ooit: {p1} stelt een gewaagde vraag. Wie het wél heeft gedaan, drinkt 3 slokken.'],
        ['type' => 'group',     'level' => 'extreem', 'need' => 0, 'text' => 'Iedereen die het aantal exen op één hand kan tellen, drinkt 3 slokken, anders 5.'],
        ['type' => 'challenge', 'level' => 'extreem', 'need' => 1, 'text' => '{p1}, laat je laatste zoekopdracht in je browser zien, of neem een shot.'],
        ['type' => 'challenge', 'level' => 'extreem', 'need' => 1, 'text' => '{p1}, bel een willekeurig contact en zing 10 sec, of neem 2 shots.'],
        ['type' => 'vote',      'level' => 'extreem', 'need' => 0, 'text' => 'Wie zou het beste in bed zijn? Stem, meeste stemmen neemt een shot.'],
        ['type' => 'vote',      'level' => 'extreem', 'need' => 0, 'text' => 'Wie heeft de meeste exen? Stem, die persoon drinkt {sips} slokken.'],
        ['type' => 'rule',      'level' => 'extreem', 'need' => 0, 'text' => 'Nieuwe regel: elke keer dat je lacht, neem je een shot. (rest van het spel)'],
        ['type' => 'rule',      'level' => 'extreem', 'need' => 2, 'text' => 'Nieuwe regel: {p1} en {p2} zijn nu drink-partners, drinkt er één, dan drinkt de ander mee.'],
        ['type' => 'duel',      'level' => 'extreem', 'need' => 2, 'text' => '{p1} vs {p2}: shot-race. Verliezer neemt nog een shot.'],
        ['type' => 'duel',      'level' => 'extreem', 'need' => 2, 'text' => '{p1} vs {p2}: wie durft de gênantste bekentenis? De groep kiest de winnaar; verliezer neemt een shot.'],

        // ---------- Extra: Gezellig ----------
        ['type' => 'direct',    'level' => 'gezellig', 'need' => 1, 'text' => '{p1}, neem een slok voor elke broer of zus die je hebt (minstens 1).'],
        ['type' => 'group',     'level' => 'gezellig', 'need' => 0, 'text' => 'Iedereen die vanochtend zijn bed heeft opgemaakt, deelt 2 slokken uit, de rest drinkt 1.'],
        ['type' => 'challenge', 'level' => 'gezellig', 'need' => 1, 'text' => '{p1}, zeg het alfabet achterstevoren tot en met de T, anders {sips} slokken.'],
        ['type' => 'vote',      'level' => 'gezellig', 'need' => 0, 'text' => 'Wie zou het langst zonder telefoon overleven? Stem, de rest van de top 3 drinkt {sips} slokken.'],
        ['type' => 'duel',      'level' => 'gezellig', 'need' => 2, 'text' => '{p1} vs {p2}: armpje drukken. Verliezer neemt {sips} slokken.'],

        // ---------- Extra: Pittig ----------
        ['type' => 'direct',    'level' => 'pittig', 'need' => 2, 'text' => '{p1}, neem {sips} slokken en kies {p2} om evenveel mee te drinken.'],
        ['type' => 'group',     'level' => 'pittig', 'need' => 0, 'text' => 'Iedereen die weleens iets uit een winkel "vergat" te betalen, drinkt 3 slokken.'],
        ['type' => 'group',     'level' => 'pittig', 'need' => 0, 'text' => 'Iedereen die deze week meer dan 5 uur op social media zat, neemt 3 slokken.'],
        ['type' => 'challenge', 'level' => 'pittig', 'need' => 1, 'text' => '{p1}, laat je meest gênante foto zien, of neem {sips} stevige slokken.'],
        ['type' => 'challenge', 'level' => 'pittig', 'need' => 2, 'text' => '{p1}, doe een verleidelijke dansmove van 5 sec richting {p2}, of drink {sips} slokken.'],
        ['type' => 'vote',      'level' => 'pittig', 'need' => 0, 'text' => 'Wie zou vanavond het snelst dronken worden? Stem, de "winnaar" drinkt {sips} slokken.'],
        ['type' => 'rule',      'level' => 'pittig', 'need' => 0, 'text' => 'Nieuwe regel: vanaf nu praat iedereen op fluistertoon. Te hard = 2 slokken.'],
        ['type' => 'duel',      'level' => 'pittig', 'need' => 2, 'text' => '{p1} vs {p2}: wie het langst zijn lach inhoudt wint. Verliezer drinkt {sips} slokken.'],

        // ---------- Extra: Extreem ----------
        ['type' => 'direct',    'level' => 'extreem', 'need' => 1, 'text' => '{p1}, sla je glas in één keer achterover. 🍻'],
        ['type' => 'direct',    'level' => 'extreem', 'need' => 2, 'text' => '{p1} en {p2}, doe samen een shot en klink erop.'],
        ['type' => 'direct',    'level' => 'extreem', 'need' => 1, 'text' => '{p1}, kies: 2 shots nu, of de volgende 3 rondes drink je dubbel.'],
        ['type' => 'group',     'level' => 'extreem', 'need' => 1, 'text' => 'Nooit-heb-ik-ooit (spicy): {p1} begint met een gewaagde. Wie het wél deed, neemt een shot.'],
        ['type' => 'group',     'level' => 'extreem', 'need' => 0, 'text' => 'Iedereen die weleens een one-night-stand heeft gehad, drinkt 4 slokken.'],
        ['type' => 'group',     'level' => 'extreem', 'need' => 0, 'text' => 'Iedereen die weleens een pikante foto heeft verstuurd, neemt een shot (of doe alsof 😇).'],
        ['type' => 'group',     'level' => 'extreem', 'need' => 0, 'text' => 'Iedereen die stiekem weleens iemand in deze kamer leuk vond, drinkt 3 slokken.'],
        ['type' => 'challenge', 'level' => 'extreem', 'need' => 1, 'text' => '{p1}, laat je laatste drie verstuurde appjes zien, of neem 2 shots.'],
        ['type' => 'challenge', 'level' => 'extreem', 'need' => 1, 'text' => '{p1}, stuur je crush een berichtje waar iedereen bij is, of neem 2 shots.'],
        ['type' => 'challenge', 'level' => 'extreem', 'need' => 2, 'text' => '{p1}, ruil een kledingstuk met {p2} voor de rest van het spel, of neem een shot.'],
        ['type' => 'challenge', 'level' => 'extreem', 'need' => 2, 'text' => '{p1}, geef {p2} 20 sec een schoudermassage, of jullie drinken allebei {sips} slokken.'],
        ['type' => 'challenge', 'level' => 'extreem', 'need' => 1, 'text' => '{p1}, vertel je gênantste dronken verhaal, of neem een shot.'],
        ['type' => 'vote',      'level' => 'extreem', 'need' => 0, 'text' => 'Wie zou de beste stripper zijn? Stem, de winnaar geeft een shot weg.'],
        ['type' => 'vote',      'level' => 'extreem', 'need' => 0, 'text' => 'Wie heeft de meeste bedpartners gehad? Stem, de top 2 nemen een shot.'],
        ['type' => 'vote',      'level' => 'extreem', 'need' => 0, 'text' => 'Wie zou als eerste vreemdgaan? Stem, die persoon neemt een shot.'],
        ['type' => 'rule',      'level' => 'extreem', 'need' => 0, 'text' => 'Nieuwe regel: elke keer dat je je telefoon oppakt, neem je een shot. (rest van het spel)'],
        ['type' => 'rule',      'level' => 'extreem', 'need' => 1, 'text' => 'Nieuwe regel: {p1} mag de rest van het spel iemand een shot geven wanneer hij of zij wil.'],
        ['type' => 'rule',      'level' => 'extreem', 'need' => 0, 'text' => 'Nieuwe regel: vloeken kost vanaf nu een shot.'],
        ['type' => 'duel',      'level' => 'extreem', 'need' => 2, 'text' => '{p1} vs {p2}: shotladder, om de beurt een shot tot iemand past. Wie past neemt er nog één.'],
        ['type' => 'duel',      'level' => 'extreem', 'need' => 2, 'text' => '{p1} vs {p2}: staarwedstrijd van dichtbij. Wie lacht of wegkijkt neemt een shot.'],
    ];

    $levels = [
        'opbouwend' => ['label' => 'Opbouwend', 'emoji' => '📈', 'desc' => 'Begint rustig, wordt pittiger', 'ring' => 'ring-sky-200',     'accent' => 'text-sky-600'],
        'gezellig'  => ['label' => 'Gezellig',  'emoji' => '😊', 'desc' => 'Luchtig en onschuldig',         'ring' => 'ring-emerald-200', 'accent' => 'text-emerald-600'],
        'pittig'    => ['label' => 'Pittig',    'emoji' => '😏', 'desc' => 'Wat gewaagder',                 'ring' => 'ring-amber-200',   'accent' => 'text-amber-600'],
        'extreem'   => ['label' => 'Extreem',   'emoji' => '🔥', 'desc' => 'Alles uit de kast',             'ring' => 'ring-rose-200',    'accent' => 'text-rose-600'],
    ];
@endphp

<x-app-layout title="Picolo">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                🥃 {{ __('Picolo') }}
            </h2>
            <a href="{{ route('games.index') }}" class="text-sm text-slate-600 hover:text-slate-900">
                &larr; {{ __('Spellen') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="picoloGame(@js(array_values($cards)), @js($types), @js($levels))" x-init="init()">
        {{-- ---------- Setup ---------- --}}
        <div x-show="state === 'setup'" class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Spelers --}}
            <div class="bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl">
                <h3 class="text-lg font-semibold text-slate-900">Wie doen er mee?</h3>
                <p class="mt-1 text-sm text-slate-500">Voeg minstens 2 spelers toe. Namen worden op dit apparaat onthouden.</p>

                <div class="mt-4 flex gap-2">
                    <input type="text" x-model="newName" @keydown.enter.prevent="addPlayer()"
                        maxlength="20" placeholder="Naam…"
                        class="flex-1 rounded-full border-slate-200 focus:border-sky-400 focus:ring-sky-400 text-sm">
                    <button type="button" @click="addPlayer()"
                        class="rounded-full bg-sky-600 px-5 py-2 text-sm font-semibold text-white hover:bg-sky-700 transition">
                        Toevoegen
                    </button>
                </div>

                <div class="mt-4 flex flex-wrap gap-2" x-show="players.length">
                    <template x-for="(p, i) in players" :key="i">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 pl-3 pr-1.5 py-1 text-sm font-medium text-slate-700">
                            <span x-text="p"></span>
                            <button type="button" @click="removePlayer(i)"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-600">&times;</button>
                        </span>
                    </template>
                </div>
                <p x-show="!players.length" class="mt-4 text-sm text-slate-400 italic">Nog geen spelers toegevoegd.</p>
            </div>

            {{-- Niveau --}}
            <div class="bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl">
                <h3 class="text-lg font-semibold text-slate-900">Kies een niveau</h3>
                <p class="mt-1 text-sm text-slate-500">Hoger niveau voegt de pittigere kaartjes toe. "Opbouwend" wordt vanzelf pittiger.</p>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ($levels as $key => $l)
                        <button type="button" @click="level = '{{ $key }}'"
                            class="text-left rounded-2xl p-4 ring-2 transition focus:outline-none"
                            :class="level === '{{ $key }}' ? '{{ $l['ring'] }} bg-slate-50 shadow-sm' : 'ring-slate-100 hover:ring-slate-200'">
                            <p class="text-2xl">{{ $l['emoji'] }}</p>
                            <p class="mt-2 text-base font-bold {{ $l['accent'] }}">{{ $l['label'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $l['desc'] }}</p>
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="button" @click="start()" :disabled="players.length < 2"
                class="w-full rounded-full bg-slate-900 px-6 py-3.5 text-base font-bold text-white transition disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-800">
                <span x-show="players.length >= 2">Start spel 🥃</span>
                <span x-show="players.length < 2">Voeg minstens 2 spelers toe</span>
            </button>
        </div>

        {{-- ---------- Spel: full-screen tik-door kaartjes ---------- --}}
        <div x-show="state === 'playing'" x-cloak
            class="fixed inset-0 z-[60] flex flex-col bg-gradient-to-b from-slate-100 to-slate-300 p-4 sm:p-6">
            <div class="mx-auto flex w-full max-w-xl flex-1 flex-col min-h-0">
                {{-- Topbalk --}}
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-500">Kaart <span x-text="round"></span></span>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="showRules = !showRules" x-show="activeRules.length"
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-700">
                            📜 <span x-text="activeRules.length"></span>
                        </button>
                        <button type="button" @click="stop()" class="font-semibold text-slate-600 hover:text-slate-900 underline underline-offset-4">Stoppen</button>
                    </div>
                </div>

                {{-- Actieve regels --}}
                <div x-show="showRules && activeRules.length" x-cloak class="mt-3 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 p-4 max-h-40 overflow-y-auto">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Actieve regels</p>
                    <ul class="mt-2 space-y-1.5 text-sm text-emerald-900 list-disc list-inside">
                        <template x-for="(r, i) in activeRules" :key="i"><li x-text="r"></li></template>
                    </ul>
                </div>

                {{-- Kaart --}}
                <div @click="next()" role="button" aria-label="Volgende kaart"
                    class="mt-4 flex flex-1 min-h-0 cursor-pointer select-none flex-col overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-slate-200">
                    <div class="h-2 w-full shrink-0" :class="meta.bar"></div>
                    <div class="flex flex-1 flex-col items-center justify-center overflow-y-auto p-8 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold" :class="meta.badge">
                            <span x-text="meta.emoji"></span>
                            <span x-text="meta.label"></span>
                        </span>
                        <p class="mt-8 text-3xl sm:text-4xl font-extrabold text-slate-900 leading-snug" x-text="card.text"></p>
                        <p class="mt-10 text-sm text-slate-400 animate-pulse">Tik voor de volgende kaart 👆</p>
                    </div>
                </div>

                <button type="button" @click="next()"
                    class="mt-4 w-full shrink-0 rounded-full bg-slate-900 px-6 py-3.5 text-base font-bold text-white hover:bg-slate-800 transition">
                    Volgende kaart →
                </button>
            </div>
        </div>
    </div>

    <script>
        window.picoloGame = function (cards, types, levels) {
            return {
                cards: cards,
                types: types,
                levelsOrder: ['gezellig', 'pittig', 'extreem'],

                // state
                state: 'setup',
                players: [],
                newName: '',
                level: 'opbouwend',
                round: 0,
                card: { type: 'direct', text: '' },
                activeRules: [],
                showRules: false,
                _recent: [],
                _lastType: null,
                _roundsSinceRule: 99,

                init() {
                    try {
                        const saved = JSON.parse(localStorage.getItem('picolo.players') || '[]');
                        if (Array.isArray(saved)) this.players = saved.filter(n => typeof n === 'string');
                    } catch (e) { /* ignore */ }
                },

                // ---------- spelers ----------
                addPlayer() {
                    const name = this.newName.trim();
                    if (!name) return;
                    if (this.players.some(p => p.toLowerCase() === name.toLowerCase())) { this.newName = ''; return; }
                    this.players.push(name);
                    this.newName = '';
                    this._save();
                },
                removePlayer(i) {
                    this.players.splice(i, 1);
                    this._save();
                },
                _save() {
                    try { localStorage.setItem('picolo.players', JSON.stringify(this.players)); } catch (e) { /* ignore */ }
                },

                // ---------- spelverloop ----------
                start() {
                    if (this.players.length < 2) return;
                    this.round = 0;
                    this.activeRules = [];
                    this.showRules = false;
                    this._recent = [];
                    this._lastType = null;
                    this._roundsSinceRule = 99;
                    this.state = 'playing';
                    this.next();
                },
                stop() {
                    this.state = 'setup';
                },

                _allowedLevels() {
                    // Opbouwend: begint gezellig en ontgrendelt gaandeweg de pittigere kaarten.
                    if (this.level === 'opbouwend') {
                        if (this.round < 6) return ['gezellig'];
                        if (this.round < 14) return ['gezellig', 'pittig'];
                        return ['gezellig', 'pittig', 'extreem'];
                    }
                    const idx = this.levelsOrder.indexOf(this.level);
                    return this.levelsOrder.slice(0, idx + 1);
                },
                _pool() {
                    const allowed = this._allowedLevels();
                    return this.cards.filter(c => allowed.includes(c.level) && (c.need || 0) <= this.players.length);
                },

                next() {
                    // Soms vervalt een bestaande regel weer (niet twee keer achter elkaar).
                    if (this.activeRules.length > 0 && this._lastType !== 'endrule' && Math.random() < 0.18) {
                        const i = Math.floor(Math.random() * this.activeRules.length);
                        const removed = this.activeRules.splice(i, 1)[0].replace(/^Nieuwe regel:\s*/i, '');
                        this.card = { type: 'endrule', text: 'Deze regel geldt niet meer: ' + removed };
                        this.round++;
                        this._lastType = 'endrule';
                        this._roundsSinceRule++;
                        return;
                    }

                    const pool = this._pool();
                    if (pool.length === 0) return;

                    // Niet exact dezelfde tekst als recent.
                    let choices = pool.filter(c => !this._recent.includes(c.text));
                    if (choices.length === 0) choices = pool;

                    // Niet hetzelfde type als de vorige kaart.
                    const differentType = choices.filter(c => c.type !== this._lastType);
                    if (differentType.length) choices = differentType;

                    // Regels niet te vaak: minstens 4 kaarten ertussen, en max 3 tegelijk actief.
                    if (this._roundsSinceRule < 4 || this.activeRules.length >= 3) {
                        const noRules = choices.filter(c => c.type !== 'rule');
                        if (noRules.length) choices = noRules;
                    }

                    const tmpl = choices[Math.floor(Math.random() * choices.length)];

                    this._recent.push(tmpl.text);
                    if (this._recent.length > 8) this._recent.shift();

                    const text = this._fill(tmpl);
                    this.card = { type: tmpl.type, text: text };
                    this.round++;
                    this._lastType = tmpl.type;
                    this._roundsSinceRule = (tmpl.type === 'rule') ? 0 : this._roundsSinceRule + 1;
                    if (tmpl.type === 'rule') {
                        // Nooit meer dan 3 regels tegelijk: de oudste vervalt.
                        if (this.activeRules.length >= 3) this.activeRules.shift();
                        this.activeRules.push(text);
                    }
                },

                _fill(tmpl) {
                    const picked = this._pickPlayers(tmpl.need || 0);
                    let t = tmpl.text;
                    t = t.replace('{p1}', picked[0] || '').replace('{p1}', picked[0] || '');
                    t = t.replace('{p2}', picked[1] || '');
                    t = t.replace('{p3}', picked[2] || '');
                    t = t.replace(/\{sips\}/g, () => String(2 + Math.floor(Math.random() * 4))); // 2–5
                    return t;
                },
                _pickPlayers(n) {
                    const pool = [...this.players];
                    for (let i = pool.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [pool[i], pool[j]] = [pool[j], pool[i]];
                    }
                    return pool.slice(0, n);
                },

                // ---------- weergave ----------
                get meta() {
                    return this.types[this.card.type] || this.types.direct;
                },
            };
        };
    </script>
</x-app-layout>
