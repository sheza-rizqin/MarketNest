@props(['title' => config('app.name', 'MarketNest')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans">
        <div class="flex min-h-screen flex-col">
            <x-site-navigation />
            @isset($header)
                <header class="border-b border-sand bg-white">
                    <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset
            <main class="flex-1">
                @if (session('status'))
                    <div class="mx-auto max-w-7xl px-5 pt-4 sm:px-8">
                        <x-alert>{{ session('status') }}</x-alert>
                    </div>
                @endif
                {{ $slot }}
            </main>
            <x-site-footer />
        </div>
    </body>
</html>
