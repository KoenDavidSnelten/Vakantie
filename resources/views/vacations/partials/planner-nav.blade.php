@php
    $plannerNavItems = [
        ['route' => 'vacations.date-planner', 'icon' => '📅', 'label' => 'Datumplanner'],
        ['route' => 'vacations.locations.index', 'icon' => '📍', 'label' => 'Locatieplanner'],
        ['route' => 'vacations.travel-planner', 'icon' => '🚗', 'label' => 'Reisplanner'],
        ['route' => 'vacations.packing-list', 'icon' => '🧳', 'label' => 'Paklijst'],
    ];
@endphp

<div class="flex flex-wrap gap-2">
    @foreach ($plannerNavItems as $item)
        <a
            href="{{ route($item['route'], $vacation) }}"
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'bg-sky-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}"
        >
            <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
        </a>
    @endforeach
</div>
