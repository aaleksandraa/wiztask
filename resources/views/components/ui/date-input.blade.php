@props([])

@php
    $model = $attributes->wire('model');
@endphp

<div
    wire:ignore
    @if ($model->hasModifier('live'))
        x-data="datePicker(@entangle($model).live)"
    @else
        x-data="datePicker(@entangle($model))"
    @endif
    {{ $attributes->whereDoesntStartWith('wire:model')->class('relative w-full') }}
>
    <input
        x-ref="input"
        type="text"
        placeholder="31.01.2026"
        autocomplete="off"
        inputmode="numeric"
        class="block w-full cursor-pointer rounded-xl border border-neutral-200 bg-white py-2.5 pl-3.5 pr-10 text-sm shadow-sm transition placeholder:text-neutral-400 focus:border-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-white dark:focus:ring-white"
    />
    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </span>
</div>
