<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button class="btn-secondary text-xs" wire:click="goToPreviousMonth">Назад</button>
            <button class="btn-secondary text-xs" wire:click="goToNextMonth">Вперёд</button>
        </div>
        <h2 class="panel-title capitalize">{{ $monthLabel }}</h2>
        <button class="btn-primary" wire:click="openEventModal">Новое событие</button>
    </div>

    <div class="panel">
        <div class="grid grid-cols-7 gap-1 text-center text-xs uppercase tracking-wide text-slate-400 mb-3">
            @foreach(['Пн','Вт','Ср','Чт','Пт','Сб','Вс'] as $dayName)
                <span>{{ $dayName }}</span>
            @endforeach
        </div>
        <div class="space-y-1">
            @foreach($weeks as $weekIndex => $week)
                <div wire:key="week-{{ $weekIndex }}" class="grid gap-1" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));">
                    @foreach($week as $day)
                        <button type="button" wire:key="day-{{ $day['date']->toDateString() }}" class="calendar-cell relative group {{ $day['isCurrentMonth'] ? '' : 'calendar-cell--muted' }}"
                                wire:click="openEventModal('{{ $day['date']->toDateString() }}')">
                            <div class="flex items-center justify-between text-xs px-2 py-1.5">
                                <span class="font-medium">{{ $day['date']->day }}</span>
                                @if($day['tasks']->count() || $day['events']->count())
                                    <span class="text-[10px] text-slate-400">{{ $day['tasks']->count() + $day['events']->count() }}</span>
                                @endif
                            </div>
                            @if($day['tasks']->count() || $day['events']->count())
                                <div class="px-2 pb-1 space-y-0.5">
                                    @foreach($day['tasks']->take(1) as $task)
                                        @php $color = $categoryPalette[$task->category] ?? '#818cf8'; @endphp
                                        <p class="truncate text-[10px] font-medium" style="color: {{ $color }}">{{ $task->title }}</p>
                                    @endforeach
                                    @foreach($day['events']->take(1) as $event)
                                        <p class="truncate text-[10px] text-slate-400">{{ $event->title }}</p>
                                    @endforeach
                                    @if($day['tasks']->count() + $day['events']->count() > 2)
                                        <p class="text-[10px] text-slate-500">+{{ $day['tasks']->count() + $day['events']->count() - 2 }}</p>
                                    @endif
                                </div>
                                <div class="calendar-tooltip text-slate-100 hidden group-hover:block">
                                    <div class="space-y-1 w-48 text-left">
                                        @foreach($day['tasks']->take(3) as $task)
                                            <div class="text-xs border-b border-slate-600 pb-1">
                                                <p class="font-semibold truncate">{{ $task->title }}</p>
                                                <a href="{{ route('workspace.tasks', ['task' => $task->id]) }}" class="text-indigo-300 hover:text-indigo-200 text-[10px]" wire:click.stop>Открыть</a>
                                            </div>
                                        @endforeach
                                        @foreach($day['events']->take(3) as $event)
                                            <div class="text-xs">
                                                <p class="font-semibold truncate">{{ $event->title }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="panel space-y-2">
        <h3 class="panel-title">События</h3>
        @forelse($events as $event)
        <div class="rounded-md border border-slate-200 px-3 py-2 flex items-center justify-between bg-white">
                <div class="flex-1">
                    <p class="font-semibold text-sm">{{ $event->title }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $event->starts_at->format('d.m H:i') }} — {{ $event->ends_at->format('H:i') }}
                    </p>
                    @if($event->description)
                        <p class="text-xs text-slate-400 truncate">{{ $event->description }}</p>
                    @endif
                </div>
                <button class="btn-secondary text-red-200 border-red-400/40 text-xs ml-2" wire:click="deleteEvent({{ $event->id }})">Удалить</button>
            </div>
        @empty
            <p class="text-sm text-slate-400">Нет событий.</p>
        @endforelse
    </div>

    @if($showEventModal)
        <div class="modal-overlay" wire:key="calendar-event-modal">
            <div class="modal-panel max-w-md">
                <div class="modal-header">
                    <h3>Новое событие</h3>
                    <button type="button" class="modal-close" wire:click="$set('showEventModal', false)">×</button>
                </div>
                <form wire:submit.prevent="createEvent" class="space-y-3">
                    <div>
                        <label class="filter-label">Название</label>
                        <input wire:model.defer="eventForm.title" type="text" class="filter-input" placeholder="Название события">
                        @error('eventForm.title') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="filter-label">Начало</label>
                            <input wire:model.defer="eventForm.starts_at" type="datetime-local" class="filter-input">
                        </div>
                        <div>
                            <label class="filter-label">Окончание</label>
                            <input wire:model.defer="eventForm.ends_at" type="datetime-local" class="filter-input">
                        </div>
                    </div>
                    <div>
                        <label class="filter-label">Цвет</label>
                        <input wire:model.defer="eventForm.color" type="color" class="filter-input h-10">
                    </div>
                    <div>
                        <label class="filter-label">Описание</label>
                        <textarea wire:model.defer="eventForm.description" rows="2" class="filter-input" placeholder="Опционально"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-secondary" wire:click="$set('showEventModal', false)">Отмена</button>
                        <button type="submit" class="btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
