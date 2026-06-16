@props(['for' => null])

<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'mb-1.5 block text-xs font-semibold uppercase tracking-[0.06em] text-neutral-500 dark:text-neutral-400']) }}>
    {{ $slot }}
</label>
