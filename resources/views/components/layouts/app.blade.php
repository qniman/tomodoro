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

    $roomsActive = str_starts_with($currentRoute ?? '', 'workspace.');
    $kanbanActive = str_starts_with($currentRoute ?? '', 'app.kanban');

    $tagsSettingsActive = $currentRoute === 'app.settings' && request()->query('tab') === 'tags';
@endphp

<x-layouts.base :title="$title">
    <div
        class="app-shell"
        x-data
        x-bind:class="{ 'app-shell--compact': $store.layout.compactSidebar, 'app-shell--mobile-drawer-open': $store.layout.mobileSidebarOpen }"
        @keydown.escape.window="$store.layout.mobileSidebarOpen && $store.layout.closeMobileSidebar()"
        x-init="
            document.addEventListener('livewire:navigated', () => Alpine.store('layout').closeMobileSidebar());
            window.addEventListener('resize', () => {
                if (window.matchMedia('(min-width: 921px)').matches) {
                    Alpine.store('layout').closeMobileSidebar();
                }
            });
        "
    >
        <header class="app-mobile-bar">
            <a href="{{ route('app') }}" wire:navigate class="app-mobile-bar__brand" aria-label="{{ config('app.name', 'Tomodoro') }}">
                <span class="sidebar__brand-mark">
                    <x-ui.icon name="tomato" :size="16" />
                </span>
            </a>
        </header>

        <div
            class="app-drawer-scrim"
            aria-hidden="true"
            x-cloak
            x-show="$store.layout.mobileSidebarOpen"
            x-transition.opacity.duration.200ms
            @click="$store.layout.closeMobileSidebar()"
        ></div>

        <div class="app-shell__body">
            <aside class="sidebar" id="app-sidebar" aria-label="Основная навигация">
            <a
                href="{{ route('app') }}"
                wire:navigate.hover
                class="sidebar__brand"
                @click="$store.layout.closeMobileSidebar()"
            >
                <span class="sidebar__brand-mark">
                    <x-ui.icon name="tomato" :size="16" />
                </span>
                <span class="sidebar__brand-title">Tomodoro</span>
            </a>

            <button
                type="button"
                class="sidebar__search"
                x-data
                @click="window.dispatchEvent(new CustomEvent('open-cmdk'))"
                title="Открыть командную палитру (Ctrl+K)"
            >
                <x-ui.icon name="search" :size="14" />
                <span class="sidebar__search-label">Поиск и команды</span>
                <span class="sidebar__search-keys">
                    <kbd class="kbd">Ctrl</kbd><kbd class="kbd">K</kbd>
                </span>
            </button>

            <nav class="sidebar__nav">
            <div class="sidebar__nav-block sidebar__nav-block--tabs-on-mobile">
                @foreach($primaryNav as $item)
                    @php
                        $isActive = $currentRoute === $item['route']
                            || ($item['route'] === 'app.inbox' && $currentRoute === 'app');
                    @endphp
                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                        wire:navigate
                        class="sidebar__link {{ $isActive ? 'is-active' : '' }}"
                        title="{{ $item['label'] }}"
                    >
                        <x-ui.icon :name="$item['icon']" :size="18" />
                        <span class="sidebar__nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <hr class="sidebar__divider" role="presentation" />
            </div>
                <a
                    href="{{ route('app.kanban') }}"
                    wire:navigate
                    class="sidebar__link {{ $kanbanActive ? 'is-active' : '' }}"
                    title="Доски"
                >
                    <x-ui.icon name="layout-kanban" :size="18" />
                    <span class="sidebar__nav-label">Доски</span>
                </a>


                <a
                    href="{{ route('app.settings', ['tab' => 'tags']) }}"
                    wire:navigate
                    class="sidebar__link {{ $tagsSettingsActive ? 'is-active' : '' }}"
                    title="Теги"
                >
                    <x-ui.icon name="tag" :size="18" />
                    <span class="sidebar__nav-label">Теги</span>
                </a>

                <livewire:workspace.sidebar-projects />

                <button
                    type="button"
                    class="sidebar__narrow sidebar__narrow--desktop-only"
                    @click.prevent="$store.layout.toggleSidebarCompact()"
                    title="Компактная боковая панель"
                >
                    <x-ui.icon name="panel-left" :size="16" />
                    <span class="sidebar__narrow-label">Компактная панель</span>
                </button>
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
                            <x-ui.icon class="sidebar__user-chevron" name="chevron-up" :size="14" />
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
                    <button type="button" class="dropdown__item" @click="$dispatch('open-support')">
                        <x-ui.icon name="circle-help" :size="16" />
                        <span>Поддержка</span>
                    </button>
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

        <nav class="app-mobile-tabbar" aria-label="Основные разделы">
            @foreach($primaryNav as $item)
                @php
                    $isTabActive = $currentRoute === $item['route']
                        || ($item['route'] === 'app.inbox' && $currentRoute === 'app');
                @endphp
                <a
                    href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                    wire:navigate
                    class="app-mobile-tabbar__tab {{ $isTabActive ? 'is-active' : '' }}"
                >
                    <span class="app-mobile-tabbar__icon">
                        <x-ui.icon :name="$item['icon']" :size="22" />
                    </span>
                    <span class="app-mobile-tabbar__label">{{ $item['label'] }}</span>
                </a>
            @endforeach
            <button
                type="button"
                class="app-mobile-tabbar__tab app-mobile-tabbar__tab--more {{ $tagsSettingsActive || $currentRoute === 'app.settings' ? 'is-active-soft' : '' }}"
                :class="{ 'is-active': $store.layout.mobileSidebarOpen }"
                aria-label="Ещё: поиск, проекты, профиль"
                @click="$store.layout.toggleMobileSidebar()"
            >
                <span class="app-mobile-tabbar__icon">
                    <x-ui.icon name="more-h" :size="22" />
                </span>
                <span class="app-mobile-tabbar__label">Ещё</span>
            </button>
        </nav>
    </div>

    <livewire:workspace.manage-projects-modal />
    <livewire:workspace.manage-tags-modal />
    <livewire:release-notes-modal />

    <div x-data="{ supportOpen: false }" @open-support.window="supportOpen = true">
    <template x-teleport="body">
        <div
            class="modal-backdrop"
            x-show="supportOpen"
            x-cloak
            x-transition.opacity.duration.150ms
            @click.self="supportOpen = false"
            @keydown.escape.window="supportOpen = false"
        >
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="support-title" x-show="supportOpen" x-transition.duration.150ms>
                <div class="modal__header">
                    <h2 class="modal__title" id="support-title">Поддержка</h2>
                    <button
                        type="button"
                        class="btn btn--ghost btn--icon btn--sm"
                        @click="supportOpen = false"
                        aria-label="Закрыть"
                    >
                        <x-ui.icon name="x" :size="16" />
                    </button>
                </div>

                <div class="modal__body" style="display: flex; flex-direction: column; gap: var(--s-4);">
                    <p style="font-size: var(--fz-sm); color: var(--text-muted); line-height: 1.6; margin: 0;">
                        Если вы столкнулись с проблемой, нашли баг или хотите предложить улучшение — напишите нам. Мы читаем каждое письмо.
                    </p>

                    <div style="display: flex; align-items: center; gap: var(--s-3); padding: var(--s-3) var(--s-4); background: var(--surface-2); border-radius: var(--radius); border: 1px solid var(--border);">
                        <x-ui.icon name="mail" :size="16" style="color: var(--text-muted); flex-shrink: 0;" />
                        <span style="font-size: var(--fz-sm); color: var(--text); font-weight: 500; flex: 1;">support@tomodoro.online</span>
                        <button
                            type="button"
                            class="btn btn--ghost btn--sm"
                            x-data="{ copied: false }"
                            @click="navigator.clipboard.writeText('support@tomodoro.online'); copied = true; setTimeout(() => copied = false, 2000)"
                        >
                            <x-ui.icon name="copy" :size="14" x-show="!copied" />
                            <x-ui.icon name="check" :size="14" x-show="copied" x-cloak style="color: var(--success);" />
                            <span x-text="copied ? 'Скопировано' : 'Копировать'"></span>
                        </button>
                    </div>
                </div>

                <div class="modal__footer">
                    <a
                        href="mailto:support@tomodoro.online"
                        class="btn btn--primary"
                    >
                        <x-ui.icon name="mail" :size="14" />
                        Написать письмо
                    </a>
                    <button type="button" class="btn btn--ghost" @click="supportOpen = false">Закрыть</button>
                </div>
            </div>
        </div>
    </template>
    </div>
</x-layouts.base>
