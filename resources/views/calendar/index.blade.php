<x-layouts.app title="Календарь">
    <section class="panel mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="panel-title">Планер</h2>
                <p class="panel-subtitle">Синхронизируйте задачи с календарём и фиксируйте рабочие блоки</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('workspace.tasks') }}" class="btn-secondary text-sm">To-Do</a>
                <a href="{{ route('workspace.timer') }}" class="btn-secondary text-sm">Pomodoro</a>
            </div>
        </div>
    </section>

    @livewire('calendar-overview')
</x-layouts.app>
