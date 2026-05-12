<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-surface-50 dark:bg-surface-900 font-sans antialiased">

<div x-data x-cloak class="flex h-full">

    {{-- Sidebar --}}
    <x-layouts.sidebar />

    {{-- Overlay mobile --}}
    <div
        x-show="$store.sidebar.open"
        x-transition.opacity
        @click="$store.sidebar.toggle()"
        class="fixed inset-0 z-20 bg-black/50 lg:hidden"
    ></div>

    {{-- Main content --}}
    <div class="flex flex-1 flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <x-layouts.topbar :title="$title ?? ''" />

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            {{ $slot }}
        </main>

    </div>
</div>

@livewireScripts
</body>
</html>
