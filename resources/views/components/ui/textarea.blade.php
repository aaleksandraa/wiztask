@props(['rows' => 3])

<textarea rows="{{ $rows }}" {{ $attributes->merge([
    'class' => 'block w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-neutral-900 focus:ring-neutral-900 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-white dark:focus:ring-white',
]) }}>{{ $slot }}</textarea>
