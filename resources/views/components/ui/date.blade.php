@props(['value' => null, 'withTime' => false, 'fallback' => '-'])

<span {{ $attributes }}>{{ \App\Support\Dates::formatOr($value, $fallback, $withTime) }}</span>
