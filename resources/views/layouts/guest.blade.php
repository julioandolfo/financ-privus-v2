<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gradient-to-br from-primary-900 via-primary-800 to-surface-900 font-sans antialiased">

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center mb-8">
            <x-ui.logo class="h-12 w-auto text-white" />
        </div>
        {{ $header ?? '' }}
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-surface-800 py-8 px-4 shadow-xl rounded-2xl sm:px-10">
            {{ $slot }}
        </div>
    </div>

    <p class="mt-8 text-center text-sm text-primary-300">
        {{ config('app.name') }} &mdash; Sistema Financeiro Empresarial
    </p>
</div>

@livewireScripts
</body>
</html>
