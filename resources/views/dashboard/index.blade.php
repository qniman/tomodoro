<x-layouts.app title="Дашборд">
    <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card">
            <p class="card-label">Задачи</p>
            <div class="card-value">{{ number_format($stats['tasks']) }}</div>
            <p class="card-caption">Активные и архивные задачи пользователя</p>
        </div>
        <div class="card">
            <p class="card-label">Помидоры</p>
            <div class="card-value">{{ number_format($stats['pomodoro_sessions']) }}</div>
            <p class="card-caption">Сессии Pomodoro за всё время</p>
        </div>
        <div class="card">
            <p class="card-label">События календаря</p>
            <div class="card-value">{{ number_format($stats['calendar_events']) }}</div>
            <p class="card-caption">Запланированные встречи и задачи</p>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="panel">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="panel-title">Фокусные задачи</h2>
                        <p class="panel-subtitle">Ближайшие задачи с дедлайнами</p>
                    </div>
                    <a href="{{ route('workspace.tasks') }}" class="btn-secondary text-sm">Открыть To-Do</a>
                </div>
                <div class="space-y-3">
                    @forelse ($upcomingTasks as $task)
                        <article class="flex items-center justify-between gap-3 rounded-md border border-slate-200 px-4 py-3 bg-white">
                            <div>
                                <p class="font-semibold">{{ $task->title }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $task->category ?? 'Без категории' }} •
                                    {{ $task->due_at?->format('d.m H:i') ?? 'Без срока' }}
                                </p>
                                <div class="flex gap-2 mt-1">
                                    @foreach($task->tags as $tag)
                                        <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full border border-slate-700" style="border-color: {{ $tag->color }}; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @php
                                $priorityLabels = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий'];
                            @endphp
                            <span class="text-xs uppercase tracking-wide {{ $task->priority === 'high' ? 'text-red-300' : ($task->priority === 'medium' ? 'text-amber-300' : 'text-emerald-300') }}">
                                {{ $priorityLabels[$task->priority] ?? ucfirst($task->priority) }}
                            </span>
                        </article>
                    @empty
                        <p class="text-sm text-slate-400">Нет активных задач. Создайте первую задачу в модуле To-Do.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="panel-title">Эвенты и встречи</h2>
                        <p class="panel-subtitle">Важные события календаря</p>
                    </div>
                    <a href="{{ route('workspace.calendar') }}" class="btn-secondary text-sm">Календарь</a>
                </div>
                <div class="space-y-3">
                    @forelse ($upcomingEvents as $event)
                        <div class="rounded-md border border-slate-200 px-4 py-3 bg-white">
                            <p class="font-semibold">{{ $event->title }}</p>
                            <p class="text-xs text-slate-400">
                                {{ \Illuminate\Support\Carbon::parse($event->starts_at)->format('d.m H:i') }}
                                —
                                {{ \Illuminate\Support\Carbon::parse($event->ends_at)->format('H:i') }}
                            </p>
                            <p class="text-sm mt-2 text-slate-300">{{ $event->description ?? 'Без описания' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Событий нет — добавьте их через модуль календаря.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="panel-title">Помидорки</h2>
                        <p class="panel-subtitle">Последние сессии</p>
                    </div>
                    <a href="{{ route('workspace.timer') }}" class="btn-secondary text-sm">Таймер</a>
                </div>
                <div class="space-y-3">
                    @forelse ($recentSessions as $session)
                        <div class="rounded-md border border-slate-200 px-4 py-3 text-sm bg-white">
                            <p class="font-semibold">{{ $session->task?->title ?? 'Без задачи' }}</p>
                            <p class="text-slate-400">{{ $session->completed_pomodoros }} / {{ $session->total_pomodoros }} циклов</p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $session->status === 'finished' ? 'Завершена' : 'В процессе' }}
                                • {{ $session->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Сессии ещё не запускались.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="panel-title mb-2">Активная сессия</h2>
                @if($activeSession)
                    <div class="space-y-3 text-sm">
                        <p class="text-lg font-semibold">{{ $activeSession->task?->title ?? 'Фокус' }}</p>
                        <p class="text-slate-400">Цикл {{ $activeSession->completed_pomodoros + 1 }} из {{ $activeSession->total_pomodoros }}</p>
                        <p class="text-slate-400">Рабочее время: {{ intdiv($activeSession->work_sec, 60) }} мин</p>
                        <div class="flex gap-2">
                            <a href="{{ route('workspace.timer') }}" class="btn-primary flex-1 text-center">Управлять</a>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400">Нет запущенных сессий. Зайдите в модуль Pomodoro и начните новый цикл.</p>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
