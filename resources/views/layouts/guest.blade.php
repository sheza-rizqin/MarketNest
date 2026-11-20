<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'MarketNest') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans">
        <div class="flex min-h-screen flex-col">
            <x-site-navigation />
            <main class="flex flex-1 items-center justify-center px-5 py-12">
                <div class="w-full max-w-md rounded-2xl border border-sand bg-white p-7 shadow-sm sm:p-9">
                    {{ $slot }}
                </div>
            </main>
            <x-site-footer />
        </div>
    </body>
</html>
