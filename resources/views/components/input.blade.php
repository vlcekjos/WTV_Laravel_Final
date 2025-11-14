@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-zluta bg-transparent text-white focus:border-yellow-400 focus:ring-yellow-400 rounded-md shadow-sm']) !!}>
