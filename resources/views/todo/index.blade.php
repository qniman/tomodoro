<x-layouts.app title="Задачи">
    <section class="panel mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="panel-title">To-Do центр</h2>
                <p class="panel-subtitle">Теги, приоритеты, оценки времени и статусы на одной доске</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('workspace.timer') }}" class="btn-secondary text-sm">Запустить Pomodoro</a>
                <a href="{{ route('workspace.calendar') }}" class="btn-secondary text-sm">Открыть календарь</a>
            </div>
        </div>
    </section>

    @livewire('todo-manager')
</x-layouts.app>
