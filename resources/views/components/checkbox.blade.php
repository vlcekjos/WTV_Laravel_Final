@props(['disabled' => false])

<!--
ZMĚNA: Nahrazeny třídy text-indigo a focus:ring-indigo za 'text-zluta' a 'focus:ring-zluta'
Pozadí ponecháno průhledné/tmavé
-->

<input type="checkbox" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
'class' => 'border-zluta dark:border-zluta text-zluta focus:ring-zluta dark:focus:ring-zluta dark:focus:ring-offset-gray-800 rounded-md shadow-sm dark:bg-black'
]) !!}>