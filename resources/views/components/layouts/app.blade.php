@props(['title' => 'Tomodoro'])

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title }} · Tomodoro</title>
    @php
        $user = auth()->user();
        $resolvedTheme = 'light';
    @endphp
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.documentElement.classList.add('light');
            localStorage.setItem('tomodoro-theme', 'light');
        });
    </script>
</head>
<body class="light bg-white text-slate-900 h-screen overflow-hidden">
@php
    $icons = [
        'dashboard' => '<svg viewBox="0 0 24 24" class="w-5 h-5"><path fill="currentColor" d="M4 4h7v7H4zm9 0h7v4h-7zm0 6h7v10h-7zM4 13h7v8H4z"/></svg>',
        'tasks' => '<svg viewBox="0 0 24 24" class="w-5 h-5"><path fill="currentColor" d="m9 11 1 1 5-5-1.4-1.4L9 9.2 6.4 6.6 5 8z"/><path fill="currentColor" d="M5 13h6v2H5zm0 4h9v2H5zm11-5h3v6h-3z"/></svg>',
        'timer' => '<svg viewBox="0 0 24 24" class="w-5 h-5"><path fill="currentColor" d="M9 2h6v2H9z"/><path fill="currentColor" d="M12 6a8 8 0 1 1 0 16 8 8 0 0 1 0-16m0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12m-1 1h2v5l3 3-1.4 1.4L11 13.4z"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" class="w-5 h-5"><path fill="currentColor" d="M7 2h2v2h6V2h2v2h3v18H4V4h3zm12 6H5v12h14zm-9 3v2H7v-2zm4 0v2h-3v-2zm4 0v2h-3v-2z"/></svg>',
        'api' => '<svg viewBox="0 0 24 24" class="w-5 h-5"><path fill="currentColor" d="M8.6 16.8 3 12l5.6-4.8 1.3 1.6L5.8 12l4.1 3.2zm6.8 0-1.3-1.6 4.1-3.2-4.1-3.2 1.3-1.6L21 12zM13 5l-2 14h2l2-14z"/></svg>',
        'sun' => '<svg viewBox="0 0 24 24" class="w-4 h-4"><path fill="currentColor" d="M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10m0-5h1v3h-1zm0 17h1v3h-1zM4.22 5.64l.7-.7L7.34 7.4l-.7.7zM16.66 7.4l2.42-2.46.7.7-2.42 2.46zM2 11h3v1H2zm17 0h3v1h-3zM5.64 18.36l2.46-2.42.7.7-2.46 2.42zm10.32-.02.7-.7 2.46 2.42-.7.7z"/></svg>',
    ];
    $navItems = [
        ['label' => 'Дашборд', 'route' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Задачи', 'route' => 'workspace.tasks', 'icon' => 'tasks'],
        ['label' => 'Помидоры', 'route' => 'workspace.timer', 'icon' => 'timer'],
        ['label' => 'Календарь', 'route' => 'workspace.calendar', 'icon' => 'calendar'],
        ['label' => 'Справочники', 'route' => 'workspace.presets', 'icon' => 'api'],
        ['label' => 'API / Интеграции', 'route' => 'workspace.api', 'icon' => 'api'],
    ];
    $user = auth()->user();
@endphp
    <div class="flex h-screen overflow-hidden">
        <aside class="hidden lg:flex lg:flex-col w-72 bg-slate-100 border-r border-r-slate-300 px-6 py-8">
            <div>
                <p class="text-xs uppercase tracking-[0.5em] text-gray-600">Tomodoro</p>
            </div>
            <nav class="mt-8 space-y-1 flex-1 overflow-y-auto pr-2">
                @foreach($navItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-[5px] text-sm transition
                              {{ $active ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-400 shadow' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-300' }}">
                        <span class="text-lg">{!! $icons[$item['icon']] !!}</span>
                        <span>{{ $item['label'] }}</span>
                        @if($active)
                            <span class="ml-auto w-2 h-2 rounded-full bg-indigo-400"></span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="mt-6 space-y-3">
                <details class="account-card">
                    <summary>
                        <div class="text-left">
                            <p class="text-sm font-semibold">{{ $user?->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $user?->email }}</p>
                        </div>
                    </summary>
                    <div class="space-y-2 mt-3">
                        <a href="{{ route('workspace.settings') }}" class="account-link">Настройки</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="account-link text-red-400 bg-red-50 border-[1px] border-red-400">Выйти</button>
                        </form>
                    </div>
                </details>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-screen">
            <header class="border-b border-b-slate-300 bg-slate-100 backdrop-blur px-4 sm:px-10 py-6 flex flex-wrap gap-4 items-center justify-between">
                <div>
                    <h1 class=" text-2xl font-semibold text-gray">{{ $title }}</h1>
                </div>
                <div class="flex items-center gap-4">
                </div>
            </header>

            <main class="flex-1 px-4 sm:px-10 py-8 space-y-6 overflow-y-auto">
                @if (session('status'))
                    <div class="rounded-[5px] border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
