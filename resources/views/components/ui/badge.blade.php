@props(['value' => null, 'map' => []])

@php
    $label = \App\Support\Options::label($map, $value, $value ?? '-');
    $classes = $value ? \App\Support\Options::badge($value) : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold tracking-wide $classes"]) }}>
    {{ $label }}
</span>
