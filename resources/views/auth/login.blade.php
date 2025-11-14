<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Plzeňské hospody" class="w-20 h-20" /> 
        </x-slot>

    <x-validation-errors class="mb-4" />

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- ZMĚNA: Přeloženo -->
        <div>
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <!-- ZMĚNA: Přeloženo -->
        <div class="mt-4">
            <x-label for="password" value="{{ __('Heslo') }}" />
            <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
        </div>

        <!-- ZMĚNA: Přeloženo -->
        <div class="block mt-4">
            <label for="remember_me" class="flex items-center">
                <x-checkbox id="remember_me" name="remember" />
                <span class="ms-2 text-sm text-gray-400">{{ __('Pamatovat si mě') }}</span>
            </label>
        </div>


        <!-- ZMĚNA: Layout spodní části a překlad -->
        <div class="flex items-center justify-between mt-4">
            
            <!-- Skupina odkazů vlevo -->
            <div class="text-sm">
                <a class="underline text-gray-400 hover:text-gray-100 rounded-md focus:outline-none" href="{{ route('password.request') }}">
                    {{ __('Zapomenuté heslo?') }}
                </a>
                
                <a class="underline text-gray-400 hover:text-gray-100 rounded-md focus:outline-none ml-4" href="{{ route('mapa') }}">
                    {{ __('Pokračovat jako anonym') }}
                </a>
            </div>

            <!-- Tlačítko vpravo -->
            <x-button class="ms-4">
                {{ __('Přihlásit se') }}
            </x-button>
        </div>

        <!-- ZMĚNA: Přeloženo a vycentrováno -->
        <div class="flex items-center justify-center mt-6">
            <a class="underline text-sm text-gray-400 hover:text-gray-100 rounded-md focus:outline-none" href="{{ route('register') }}">
                {{ __('Ještě nemáte účet? Registrovat') }}
            </a>
        </div>
    </form>
</x-authentication-card>
</x-guest-layout>
