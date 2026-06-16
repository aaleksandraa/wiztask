@props(['href' => null, 'icon' => null])

@php
    $classes = 'group flex items-center gap-3 surface surface-hover p-4';
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-neutral-100 text-neutral-700 transition group-hover:bg-neutral-900 group-hover:text-white dark:bg-neutral-800 dark:text-neutral-200 dark:group-hover:bg-white dark:group-hover:text-neutral-900">
                {!! $icon !!}
            </span>
        @endif
        <span class="min-w-0">
            <span class="block text-sm font-semibold text-neutral-900 dark:text-white">{{ $slot }}</span>
            @isset($sub)
                <span class="mt-0.5 block text-xs text-neutral-500 dark:text-neutral-400">{{ $sub }}</span>
            @endisset
        </span>
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-neutral-100 text-neutral-700 transition group-hover:bg-neutral-900 group-hover:text-white dark:bg-neutral-800 dark:text-neutral-200 dark:group-hover:bg-white dark:group-hover:text-neutral-900">
                {!! $icon !!}
            </span>
        @endif
        <span class="min-w-0 text-left">
            <span class="block text-sm font-semibold text-neutral-900 dark:text-white">{{ $slot }}</span>
            @isset($sub)
                <span class="mt-0.5 block text-xs text-neutral-500 dark:text-neutral-400">{{ $sub }}</span>
            @endisset
        </span>
    </button>
@endif
