<x-layouts.app title="Настройки">
    <section class="panel space-y-6 max-w-3xl">
        <div>
            <h2 class="panel-title">Профиль</h2>
            <p class="panel-subtitle">Управляйте личными параметрами и внешним видом сервиса.</p>
        </div>
        <form class="space-y-4" method="POST" action="{{ route('workspace.settings.update') }}">
            @csrf
            <div>
                <label class="filter-label">Имя</label>
                <input type="text" value="{{ auth()->user()->name }}" class="filter-input" disabled>
            </div>
            <div>
                <label class="filter-label">Email</label>
                <input type="email" value="{{ auth()->user()->email }}" class="filter-input" disabled>
            </div>
{{--            <div>--}}
{{--                <label class="filter-label">Тема интерфейса</label>--}}
{{--                <select name="theme" class="filter-input">--}}
{{--                    <option value="dark" @selected(auth()->user()->theme === 'dark')>Тёмная</option>--}}
{{--                    <option value="light" @selected(auth()->user()->theme === 'light')>Светлая</option>--}}
{{--                </select>--}}
{{--            </div>--}}
            <div class="flex justify-end gap-3">
                <button type="submit" class="btn-primary">Сохранить</button>
            </div>
        </form>
    </section>
</x-layouts.app>
