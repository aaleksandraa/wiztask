@props(['label', 'value', 'sub' => null, 'color' => 'neutral'])

@php
    $colors = [
        'neutral' => 'text-neutral-900 dark:text-white',
        'indigo' => 'text-neutral-900 dark:text-white',
        'green' => 'text-emerald-600 dark:text-emerald-400',
        'amber' => 'text-amber-600 dark:text-amber-400',
        'red' => 'text-red-600 dark:text-red-400',
        'blue' => 'text-neutral-800 dark:text-neutral-200',
        'purple' => 'text-neutral-700 dark:text-neutral-300',
    ];
@endphp

<div class="surface surface-hover p-5">
    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-neutral-500 dark:text-neutral-400">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold tracking-tight {{ $colors[$color] ?? $colors['neutral'] }}">{{ $value }}</p>
    @if ($sub)
        <p class="mt-1 text-xs text-neutral-400">{{ $sub }}</p>
    @endif
</div>
