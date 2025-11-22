<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style='bg-black-90'>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite([
            'resources/css/app.css', 
            'resources/js/app.js', 
            'resources/sass/app.scss'
            ])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="bg-bar-pozadi bg-cover bg-center bg-fixed min-h-screen">
        <div class="font-sans text-gray-300 antialiased">
            {{ $slot }}
        </div>

        @livewireScripts
        <!--  
        <footer>
        
        </footer>
        -->
    </body>
</html>
