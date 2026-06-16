@props(['options' => [], 'placeholder' => null, 'selected' => null])

<select {{ $attributes->merge([
    'class' => 'block w-full rounded-xl border-neutral-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-white dark:focus:ring-white',
]) }}>
    @if (! is_null($placeholder))
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $key => $label)
        <option value="{{ $key }}" @selected((string) $selected === (string) $key)>{{ $label }}</option>
    @endforeach
    {{ $slot }}
</select>
