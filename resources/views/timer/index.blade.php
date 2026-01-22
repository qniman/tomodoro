<x-layouts.app title="Pomodoro таймер">
    <section class="panel mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="panel-title">Режим глубокой работы</h2>
                <p class="panel-subtitle">Настраивайте длительность спринта, перерывы и привязку к задачам</p>
            </div>
            <a href="{{ route('workspace.tasks') }}" class="btn-secondary text-sm">К списку задач</a>
        </div>
    </section>

    @livewire('pomodoro-timer')
</x-layouts.app>
