@php
    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['route' => 'clients.index', 'label' => 'Klijenti', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 4a3 3 0 11-6 0 3 3 0 016 0z'],
        ['route' => 'projects.index', 'label' => 'Projekti', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
        ['route' => 'tasks.index', 'label' => 'Taskovi', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['route' => 'time.index', 'label' => 'Vrijeme', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'reports.index', 'label' => 'Izvještaji', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['route' => 'settings.edit', 'label' => 'Podešavanja', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-40 w-64 transform border-r border-neutral-800/80 bg-neutral-950 text-neutral-300 shadow-2xl transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-16 items-center gap-3 border-b border-neutral-800/80 px-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-sm font-bold text-neutral-900 shadow-soft">W</div>
        <div class="min-w-0">
            <span class="block truncate text-base font-bold tracking-tight text-white">{{ \App\Support\AppSettings::appName() }}</span>
            <span class="block text-[10px] uppercase tracking-[0.12em] text-neutral-500">Task manager</span>
        </div>
    </div>
    <nav class="space-y-1 px-3 py-5">
        @foreach ($nav as $item)
            @php $active = request()->routeIs($item['route']) || ($item['route'] === 'clients.index' && request()->routeIs('clients.*')) || ($item['route'] === 'projects.index' && request()->routeIs('projects.*')) || ($item['route'] === 'tasks.index' && request()->routeIs('tasks.*')); @endphp
            <a href="{{ route($item['route']) }}" wire:navigate
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                      {{ $active ? 'bg-white text-neutral-900 shadow-soft' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
    <div class="absolute bottom-0 w-full border-t border-neutral-800/80 px-5 py-4 text-[11px] text-neutral-500">
        Interni alat · {{ now()->year }}
    </div>
</aside>
