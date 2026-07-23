@props(['value'])

<label {{ $attributes->merge(['class' => 'text-white block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
