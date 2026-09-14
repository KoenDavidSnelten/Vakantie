@php
    $plannerNavItems = [
        ['route' => 'vacations.date-planner', 'icon' => '📅', 'label' => 'Datumplanner'],
        ['route' => 'vacations.locations.index', 'icon' => '📍', 'label' => 'Locatieplanner'],
        ['route' => 'vacations.travel-planner', 'icon' => '🚗', 'label' => 'Reisplanner'],
        ['route' => 'vacations.packing-list', 'icon' => '🧳', 'label' => 'Paklijst'],
    ];
@endphp

<nav class="flex flex-wrap gap-2" aria-label="Planners">
    @foreach ($plannerNavItems as $item)
        @php $isCurrent = request()->routeIs($item['route']); @endphp
        <a
            href="{{ route($item['route'], $vacation) }}"
            @if ($isCurrent) aria-current="page" @endif
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $isCurrent ? 'bg-sky-600 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50' }}"
        >
            <span aria-hidden="true">{{ $item['icon'] }}</span> {{ $item['label'] }}
        </a>
    @endforeach
</nav>
