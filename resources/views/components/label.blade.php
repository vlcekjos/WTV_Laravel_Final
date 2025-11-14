@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-zluta']) }}>
    {{ $value ?? $slot }}
</label>
