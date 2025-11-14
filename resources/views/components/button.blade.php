@props(['disabled' => false])

<!--
ZMĚNA: Nahrazeny třídy bg-gray, dark:bg-gray atd. za 'bg-zluta text-black'
-->

<button {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
'type' => 'submit',
'class' => 'inline-flex items-center px-4 py-2 bg-zluta border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-zluta/80 focus:bg-zluta/90 active:bg-zluta/70 focus:outline-none focus:ring-2 focus:ring-zluta focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150'
]) !!}>
{{ $slot }}
</button>
