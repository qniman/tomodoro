<div class="ws ws--full">
    <div class="ws__header">
        <div class="ws__head-left">
            <h1 class="ws__title">Календарь</h1>
            <span class="ws__subtitle cal__title">
                @if($view === 'year')
                    {{ $cursorDate->isoFormat('YYYY') }}
                @elseif($view === 'week')
                    @php
                        $start = $weekData['start'];
                        $end = $start->endOfWeek(\Carbon\Carbon::SUNDAY);
                    @endphp
                    {{ $start->isoFormat('D MMM') }} – {{ $end->isoFormat('D MMM YYYY') }}
                @else
                    {{ $cursorDate->isoFormat('MMMM YYYY') }}
                @endif
            </span>
        </div>
        <div class="ws__head-right">
            <div class="seg" role="tablist">
                @foreach(['year' => 'Год', 'month' => 'Месяц', 'week' => 'Неделя'] as $key => $label)
                    <button type="button"
                            class="seg__btn {{ $view === $key ? 'is-active' : '' }}"
                            wire:click="setView('{{ $key }}')">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="cal__nav">
                <button type="button" wire:click="nav(-1)" aria-label="Назад">
                    <x-ui.icon name="chevron-left" :size="16" />
                </button>
                <button type="button" wire:click="goToday">Сегодня</button>
                <button type="button" wire:click="nav(1)" aria-label="Вперёд">
                    <x-ui.icon name="chevron-right" :size="16" />
                </button>
            </div>

            <x-ui.button variant="primary" icon="plus" wire:click="openCreateEvent">
                Создать событие
            </x-ui.button>
        </div>
    </div>

    <div class="ws__body">
        <div class="cal {{ $dayItems ? '' : 'cal--solo' }}">
            <div>
                {{-- ===== Year view ===== --}}
                @if($view === 'year' && $yearData)
                    <div class="cal-year">
                        @foreach($yearData['months'] as $month)
                            <button type="button" class="cal-year__month" wire:click="openMonthView('{{ $month['first']->toDateString() }}')">
                                <div class="cal-year__month-title">{{ $month['first']->isoFormat('MMMM') }}</div>
                                <div class="cal-year__grid">
                                    @foreach(['П','В','С','Ч','П','С','В'] as $w)
                                        <div class="cal-year__day-head">{{ $w }}</div>
                                    @endforeach
                                    @foreach($month['cells'] as $cell)
                                        <div class="cal-year__day {{ $cell['hasItems'] ? 'has-items' : '' }} {{ $cell['isToday'] ? 'is-today' : '' }} {{ ! $cell['isCurrentMonth'] ? 'is-other-month' : '' }}">
                                            {{ $cell['date']->day }}
                                        </div>
                                    @endforeach
                                </div>
                            </button>
                        @endforeach
                    </div>

                {{-- ===== Month view ===== --}}
                @elseif($view === 'month' && $monthData)
                    <div class="cal-month">
                        <div class="cal-month__weekdays">
                            @foreach(['Пн','Вт','Ср','Чт','Пт','Сб','Вс'] as $w)
                                <div class="cal-weekday">{{ $w }}</div>
                            @endforeach
                        </div>
                        <div class="cal-month__grid">
                            @foreach($monthData['weeks'] as $week)
                                @foreach($week as $day)
                                    <button type="button"
                                            class="cal-day
                                                {{ $day['isToday'] ? 'is-today' : '' }}
                                                {{ ! $day['isCurrentMonth'] ? 'is-other-month' : '' }}
                                                {{ $selectedDate === $day['key'] ? 'is-selected' : '' }}"
                                            wire:click="selectDay('{{ $day['key'] }}')"
                                            wire:dblclick="openCreateEvent('{{ $day['key'] }}')">
                                        <span class="cal-day__num">{{ $day['date']->day }}</span>
                                        @if($day['eventsCount'] || $day['tasksCount'])
                                            <div class="cal-day__events">
                                                @php $shown = 0; @endphp
                                                @foreach($day['events']->take(2) as $event)
                                                    <span class="cal-day__pill" title="{{ $event->title }}">
                                                        @unless($event->all_day)
                                                            <span class="cal-day__pill-dot" style="background: {{ $event->color ?: 'currentColor' }};"></span>
                                                            <span style="font-family: var(--font-mono); opacity: .7;">{{ $event->starts_at->format('H:i') }}</span>
                                                        @endunless
                                                        <span style="overflow: hidden; text-overflow: ellipsis;">{{ $event->title }}</span>
                                                    </span>
                                                    @php $shown++; @endphp
                                                @endforeach
                                                @foreach($day['tasks']->where('completed_at', null)->take(max(0, 2 - $shown)) as $task)
                                                    <span class="cal-day__pill cal-day__pill--task" title="{{ $task->title }}">
                                                        <x-ui.icon name="check-2" :size="9" />
                                                        <span style="overflow: hidden; text-overflow: ellipsis;">{{ $task->title }}</span>
                                                    </span>
                                                @endforeach
                                                @php $more = max(0, ($day['eventsCount'] + $day['tasksCount']) - 2); @endphp
                                                @if($more > 0)
                                                    <span class="cal-day__more">+ {{ $more }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                {{-- ===== Week view ===== --}}
                @elseif($view === 'week' && $weekData)
                    <div class="cal-week">
                        <div class="cal-week__head">
                            <div></div>
                            @foreach($weekData['days'] as $day)
                                <button type="button"
                                        class="cal-week__head-cell {{ $day['isToday'] ? 'is-today' : '' }}"
                                        wire:click="selectDay('{{ $day['key'] }}')">
                                    {{ $day['date']->isoFormat('dd') }}
                                    <span class="cal-week__head-day-num">{{ $day['date']->day }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="cal-week__body">
                            <div class="cal-week__hours">
                                @for($h = 0; $h < 24; $h++)
                                    <div class="cal-week__hour">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</div>
                                @endfor
                            </div>
                            @foreach($weekData['days'] as $day)
                                <div class="cal-week__col">
                                    @for($h = 0; $h < 24; $h++)
                                        <div class="cal-week__slot" wire:click="openCreateEvent('{{ $day['key'] }}')"></div>
                                    @endfor
                                    @foreach($day['events'] as $event)
                                        <div class="cal-week__event"
                                             style="top: {{ $event['top'] }}px; height: {{ $event['height'] }}px; background: {{ $event['color'] }};"
                                             wire:click.stop="editEvent({{ $event['id'] }})"
                                             title="{{ $event['title'] }}">
                                            <div style="font-weight: var(--fw-semibold); font-family: var(--font-mono);">{{ $event['time'] }}</div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $event['title'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ===== Side panel: day drill-down ===== --}}
            @if($dayItems)
                <aside class="cal-side">
                    <div class="hstack" style="justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div class="cal-side__title">{{ $dayItems['day']->isoFormat('D MMMM') }}</div>
                            <div class="cal-side__meta">{{ $dayItems['day']->isoFormat('dddd') }} · {{ $dayItems['tasks']->count() }} задач · {{ $dayItems['events']->count() }} событий</div>
                        </div>
                        <button type="button" class="btn btn--ghost btn--icon btn--sm" wire:click="clearSelection" aria-label="Закрыть">
                            <x-ui.icon name="x" :size="14" />
                        </button>
                    </div>

                    <x-ui.button size="sm" variant="primary" icon="plus" wire:click="openCreateEvent('{{ $dayItems['day']->toDateString() }}')">
                        Добавить событие
                    </x-ui.button>

                    <div>
                        <div class="cal-side__group-title">События</div>
                        @if($dayItems['events']->isEmpty())
                            <div class="cal-side__empty">Никаких событий — день в твоём распоряжении.</div>
                        @else
                            <div class="cal-side__list">
                                @foreach($dayItems['events'] as $event)
                                    <div class="cal-event-row" wire:click="editEvent({{ $event->id }})">
                                        <div class="cal-event-row__bar" style="background: {{ $event->color ?: 'var(--accent)' }};"></div>
                                        <div class="cal-event-row__time">
                                            @if($event->all_day)
                                                Весь день
                                            @else
                                                {{ $event->starts_at->format('H:i') }}
                                            @endif
                                        </div>
                                        <div class="cal-event-row__title">{{ $event->title }}</div>
                                        <div class="cal-event-row__actions">
                                            <button type="button"
                                                    class="btn btn--ghost btn--icon btn--sm"
                                                    wire:click.stop="deleteEvent({{ $event->id }})"
                                                    wire:confirm="Удалить событие?">
                                                <x-ui.icon name="trash" :size="14" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="cal-side__group-title">Задачи к этому дню</div>
                        @if($dayItems['tasks']->isEmpty())
                            <div class="cal-side__empty">Без дедлайна на этот день.</div>
                        @else
                            <div class="cal-side__list">
                                @foreach($dayItems['tasks'] as $task)
                                    <a href="{{ route('app.all', ['task' => $task->id]) }}" wire:navigate class="cal-event-row">
                                        <div class="cal-event-row__bar" style="background: {{ $task->completed_at ? 'var(--success)' : ($task->priority === 'high' ? 'var(--accent)' : 'var(--info)') }};"></div>
                                        <div class="cal-event-row__time">{{ $task->due_at->format('H:i') }}</div>
                                        <div class="cal-event-row__title">
                                            @if($task->completed_at)<s>{{ $task->title }}</s>@else{{ $task->title }}@endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </aside>
            @endif
        </div>
    </div>

    {{-- ===== Event modal ===== --}}
    <x-ui.modal :show="$showEventModal" size="lg"
                close-action="closeEventModal"
                :title="$editingEventId ? 'Редактировать событие' : 'Новое событие'"
                subtitle="Совет: двойной клик по дате тоже создаёт событие">
        <form wire:submit.prevent="saveEvent" class="vstack gap-4">
            <x-ui.input label="Название" wire:model="eventTitle" autofocus required maxlength="200" />

            <x-ui.textarea label="Описание" wire:model="eventDescription" rows="3" placeholder="Необязательно — пара слов о событии" />

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--s-3);">
                <x-ui.input type="datetime-local" label="Начало" wire:model="eventStartsAt" required />
                <x-ui.input type="datetime-local" label="Окончание" wire:model="eventEndsAt" required />
            </div>

            <div class="hstack gap-3" style="justify-content: space-between;">
                <x-ui.checkbox wire:model="eventAllDay" label="Весь день" />
                <div class="hstack gap-2">
                    <span class="text-xs text-muted">Цвет</span>
                    @foreach(['#E5533A','#3B82F6','#10B981','#F59E0B','#8B5CF6','#EC4899'] as $color)
                        <button type="button"
                                wire:click="$set('eventColor', '{{ $color }}')"
                                class="cal-color-dot {{ $eventColor === $color ? 'is-active' : '' }}"
                                style="background: {{ $color }};"
                                aria-label="Цвет"></button>
                    @endforeach
                </div>
            </div>
        </form>

        <x-slot:footer>
            @if($editingEventId)
                <x-ui.button variant="ghost" icon="trash" wire:click="deleteEvent({{ $editingEventId }})" wire:confirm="Удалить событие?" style="color: var(--danger);">Удалить</x-ui.button>
            @endif
            <span style="flex: 1;"></span>
            <x-ui.button variant="ghost" wire:click="closeEventModal">Отмена</x-ui.button>
            <x-ui.button variant="primary" icon="check" wire:click="saveEvent" wireTarget="saveEvent">
                {{ $editingEventId ? 'Сохранить' : 'Создать' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
