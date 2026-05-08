@props([
    'title' => null,
])

@php
    $serverTheme = auth()->user()?->theme ?? 'auto';
@endphp

<!DOCTYPE html>
<html lang="ru" data-theme="{{ $serverTheme }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? config('app.name', 'Tomodoro') }} · Tomodoro</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak]{display:none!important}</style>

    <script>
        // Раннее переключение темы, чтобы не было «вспышки светлого/тёмного».
        // Приоритет — серверное значение (от профиля), fallback — localStorage, fallback — auto.
        (function () {
            try {
                const server = @json($serverTheme);
                const stored = localStorage.getItem('tomodoro:theme');
                const theme = server || stored || 'auto';
                document.documentElement.dataset.theme = theme;
                localStorage.setItem('tomodoro:theme', theme);
            } catch (e) { /* ignore */ }
        })();
    </script>
</head>
<body>
    {{ $slot }}

    @auth
        <livewire:pomodoro.floating-timer />
    @endauth

    {{-- Глобальные тосты доступны на любой странице --}}
    <x-ui.toast-region />

    {{-- Командная палитра ⌘K --}}
    <x-ui.command-palette />

    {{-- До Livewire/Alpine: отложенный app.js (module) иначе не успевает выставить window.pomoWidget --}}
    @if (file_exists(public_path('build/pomodoro-boot.js')))
        <script src="{{ asset('build/pomodoro-boot.js') }}"></script>
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>
