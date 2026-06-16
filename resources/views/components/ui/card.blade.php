@props(['title' => null, 'padding' => 'p-5'])

<div {{ $attributes->merge(['class' => 'surface surface-hover']) }}>
    @if ($title)
        <div class="border-b border-neutral-200/80 px-5 py-3.5 dark:border-neutral-800">
            <h3 class="font-semibold tracking-tight text-neutral-900 dark:text-neutral-100">{{ $title }}</h3>
        </div>
    @endif
    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
