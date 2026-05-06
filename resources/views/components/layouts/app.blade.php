@props(['title' => null])

@php
    $user = auth()->user();
    $currentRoute = request()->route()?->getName();

    $primaryNav = [
        ['key' => 'today',    'label' => 'Сегодня',    'icon' => 'today',    'route' => 'app.today'],
        ['key' => 'inbox',    'label' => 'Входящие',   'icon' => 'inbox',    'route' => 'app.inbox'],
        ['key' => 'upcoming', 'label' => 'Предстоит',  'icon' => 'cal-week', 'route' => 'app.upcoming'],
        ['key' => 'calendar', 'label' => 'Календарь',  'icon' => 'calendar', 'route' => 'app.calendar'],
    ];

    $projects = $user?->projects()->where('is_archived', false)->orderBy('position')->get() ?? collect();
@endphp

<x-layouts.base :title="$title">
    <div class="app-shell">
        <aside class="sidebar" aria-label="Основная навигация">
            <a href="{{ route('app') }}" wire:navigate.hover class="sidebar__brand">
                <span class="sidebar__brand-mark">
                    <x-ui.icon name="tomato" :size="16" />
                </span>
                <span>Tomodoro</span>
            </a>

            <button
                type="button"
                class="sidebar__search"
                x-data
                @click="window.dispatchEvent(new CustomEvent('open-cmdk'))"
                title="Открыть командную палитру (Ctrl+K)"
            >
                <x-ui.icon name="search" :size="14" />
                <span>Поиск и команды</span>
                <span class="sidebar__search-keys">
                    <kbd class="kbd">Ctrl</kbd><kbd class="kbd">K</kbd>
                </span>
            </button>

            <nav class="sidebar__nav">
                @foreach($primaryNav as $item)
                    @php
                        $isActive = $currentRoute === $item['route']
                            || ($item['route'] === 'app.inbox' && $currentRoute === 'app');
                    @endphp
                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                        wire:navigate
                        class="sidebar__link {{ $isActive ? 'is-active' : '' }}"
                    >
                        <x-ui.icon :name="$item['icon']" :size="18" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach

                @if($projects->count())
                    <div class="sidebar__section">
                        <div class="sidebar__section-title">
                            <span>Проекты</span>
                            <button class="btn btn--ghost btn--icon btn--sm" type="button" aria-label="Новый проект">
                                <x-ui.icon name="plus" :size="14" />
                            </button>
                        </div>
                        @foreach($projects as $project)
                            <a
                                href="#"
                                wire:navigate
                                class="sidebar__link"
                            >
                                <span class="sidebar__project-dot" style="background: {{ $project->color }}"></span>
                                <span>{{ $project->name }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </nav>

            <div class="sidebar__footer">
                <x-ui.dropdown align="left" direction="up" :width="220">
                    <x-slot:trigger>
                        <button type="button" class="sidebar__user">
                            <x-ui.avatar :name="$user?->name" :src="$user?->avatar_url" size="sm" />
                            <span class="sidebar__user-info">
                                <span class="sidebar__user-name">{{ $user?->name }}</span>
                                <span class="sidebar__user-email">{{ $user?->email }}</span>
                            </span>
                            <x-ui.icon name="chevron-up" :size="14" />
                        </button>
                    </x-slot:trigger>

                    <a href="{{ route('app.settings') }}" wire:navigate class="dropdown__item">
                        <x-ui.icon name="user" :size="16" />
                        <span>Профиль</span>
                    </a>
                    <a href="{{ route('app.settings', ['tab' => 'appearance']) }}" wire:navigate class="dropdown__item">
                        <x-ui.icon name="sun-medium" :size="16" />
                        <span>Внешний вид</span>
                    </a>
                    <div class="dropdown__separator"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown__item dropdown__item--danger">
                            <x-ui.icon name="log-out" :size="16" />
                            <span>Выйти</span>
                        </button>
                    </form>
                </x-ui.dropdown>
            </div>
        </aside>

        <div class="workspace">
            {{ $slot }}
        </div>
    </div>
</x-layouts.base>
