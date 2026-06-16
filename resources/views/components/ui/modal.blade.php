@props(['title' => null, 'maxWidth' => 'max-w-2xl'])

@php
    $show = $attributes->wire('model');
@endphp

<div x-data="{ show: @entangle($show).live }"
     x-show="show"
     x-on:keydown.escape.window="show = false"
     style="display:none"
     class="fixed inset-0 z-50 overflow-y-auto">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-neutral-950/60 backdrop-blur-sm" @click="show = false"></div>

    <div class="flex min-h-screen items-start justify-center p-4 sm:pt-20">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative w-full {{ $maxWidth }} surface shadow-card">
            @if ($title)
                <div class="flex items-center justify-between border-b border-neutral-200/80 px-6 py-4 dark:border-neutral-800">
                    <h3 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">{{ $title }}</h3>
                    <button type="button" @click="show = false" class="rounded-lg p-1.5 text-neutral-400 transition hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            <div class="px-6 py-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
