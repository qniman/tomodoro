<?php

namespace App\Livewire\Workspace;

use App\Models\CalendarEvent;
use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Календарь')]
class CalendarView extends Component
{
    /** year | month | week */
    #[Url(as: 'view')]
    public string $view = 'month';

    /** ISO-дата курсора (любой день в текущем периоде). */
    #[Url(as: 'd')]
    public string $cursor = '';

    /** Выбранный день для drill-down. */
    public ?string $selectedDate = null;

    /* ===== Форма события ===== */
    public bool $showEventModal = false;
    public ?int $editingEventId = null;

    #[Validate('required|string|max:200')]
    public string $eventTitle = '';

    public string $eventDescription = '';

    #[Validate('required|date')]
    public string $eventStartsAt = '';

    #[Validate('required|date|after_or_equal:eventStartsAt')]
    public string $eventEndsAt = '';

    public bool $eventAllDay = false;

    public string $eventColor = '#E5533A';

    public function mount(): void
    {
        $this->ensureValidCursor();
        if (! in_array($this->view, ['year', 'month', 'week'], true)) {
            $this->view = 'month';
        }
    }

    public function render()
    {
        $this->ensureValidCursor();

        return view('livewire.workspace.calendar-view', [
            'cursorDate' => $this->cursorImmutable(),
            'today' => CarbonImmutable::today(),
            'monthData' => $this->view === 'month' ? $this->buildMonth() : null,
            'weekData' => $this->view === 'week' ? $this->buildWeek() : null,
            'yearData' => $this->view === 'year' ? $this->buildYear() : null,
            'dayItems' => $this->selectedDate ? $this->dayItems($this->selectedDate) : null,
        ]);
    }

    protected function ensureValidCursor(): void
    {
        if ($this->cursor === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $this->cursor)) {
            $this->cursor = now()->toDateString();
            return;
        }
        try {
            CarbonImmutable::parse($this->cursor);
        } catch (\Throwable $e) {
            $this->cursor = now()->toDateString();
        }
    }

    protected function cursorImmutable(): CarbonImmutable
    {
        try {
            return CarbonImmutable::parse($this->cursor);
        } catch (\Throwable $e) {
            return CarbonImmutable::today();
        }
    }

    /* ===== Навигация ===== */

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['year', 'month', 'week'], true) ? $view : 'month';
    }

    public function goToday(): void
    {
        $this->cursor = now()->toDateString();
        $this->selectedDate = $this->cursor;
    }

    public function nav(int $direction): void
    {
        $cursor = $this->cursorImmutable();
        $cursor = match ($this->view) {
            'year' => $cursor->addYears($direction),
            'week' => $cursor->addWeeks($direction),
            default => $cursor->addMonths($direction),
        };
        $this->cursor = $cursor->toDateString();
    }

    public function selectDay(string $date): void
    {
        $this->selectedDate = $date;
        $this->cursor = $date;
    }

    public function clearSelection(): void
    {
        $this->selectedDate = null;
    }

    public function openMonthView(string $date): void
    {
        $this->view = 'month';
        $this->cursor = $date;
    }

    /* ===== Структуры данных ===== */

    protected function buildMonth(): array
    {
        $cursor = $this->cursorImmutable()->startOfMonth();
        $start = $cursor->startOfWeek(Carbon::MONDAY);
        // Фиксируем 6 рядов = 42 дня, чтобы сетка не «прыгала» между месяцами.
        $end = $start->addDays(41);

        [$tasksByDate, $eventsByDate] = $this->itemsBetween($start, $end);

        $weeks = [];
        $week = [];
        $day = $start;
        while ($day->lte($end)) {
            $key = $day->toDateString();
            $tasks = $tasksByDate->get($key, collect());
            $events = $eventsByDate->get($key, collect());
            $week[] = [
                'date' => $day,
                'key' => $key,
                'isCurrentMonth' => $day->month === $cursor->month,
                'isToday' => $day->isToday(),
                'tasksCount' => $tasks->count(),
                'tasksDoneCount' => $tasks->whereNotNull('completed_at')->count(),
                'eventsCount' => $events->count(),
                'tasks' => $tasks,
                'events' => $events,
            ];
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
            $day = $day->addDay();
        }

        return ['cursor' => $cursor, 'weeks' => $weeks];
    }

    protected function buildYear(): array
    {
        $cursor = $this->cursorImmutable()->startOfYear();
        $monthStart = $cursor;
        $yearEnd = $cursor->endOfYear();

        [$tasksByDate, $eventsByDate] = $this->itemsBetween($monthStart, $yearEnd);

        $months = [];
        for ($m = 0; $m < 12; $m++) {
            $first = $monthStart->addMonths($m);
            $start = $first->startOfWeek(Carbon::MONDAY);
            // Всегда 6 строк × 7 дней = 42 ячейки, чтобы все карточки были одинакового размера.
            $end = $start->addDays(41);

            $cells = [];
            $day = $start;
            while ($day->lte($end)) {
                $key = $day->toDateString();
                $hasItems = $tasksByDate->has($key) || $eventsByDate->has($key);
                $cells[] = [
                    'date' => $day,
                    'isCurrentMonth' => $day->month === $first->month,
                    'isToday' => $day->isToday(),
                    'hasItems' => $hasItems,
                ];
                $day = $day->addDay();
            }

            $months[] = [
                'first' => $first,
                'cells' => $cells,
            ];
        }

        return ['cursor' => $cursor, 'months' => $months];
    }

    protected function buildWeek(): array
    {
        $cursor = $this->cursorImmutable();
        $start = $cursor->startOfWeek(Carbon::MONDAY);
        $end = $start->endOfWeek(Carbon::SUNDAY);

        [$tasksByDate, $eventsByDate] = $this->itemsBetween($start, $end);

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $start->addDays($i);
            $key = $day->toDateString();

            $events = ($eventsByDate->get($key) ?? collect())->map(function ($event) {
                $start = Carbon::parse($event->starts_at);
                $end = Carbon::parse($event->ends_at);
                $startMinutes = $start->hour * 60 + $start->minute;
                $endMinutes = max($startMinutes + 15, $end->hour * 60 + $end->minute);
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'color' => $event->color,
                    'top' => $startMinutes,
                    'height' => $endMinutes - $startMinutes,
                    'time' => $start->format('H:i') . '–' . $end->format('H:i'),
                ];
            })->toArray();

            $days[] = [
                'date' => $day,
                'key' => $key,
                'isToday' => $day->isToday(),
                'events' => $events,
            ];
        }

        return ['start' => $start, 'days' => $days];
    }

    protected function itemsBetween(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $tasks = Task::forUser(Auth::id())
            ->whereBetween('due_at', [$start, $end])
            ->get();

        $events = CalendarEvent::where('user_id', Auth::id())
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get();

        $tasksByDate = $tasks->groupBy(fn ($t) => $t->due_at->toDateString());
        $eventsByDate = $events->groupBy(fn ($e) => $e->starts_at->toDateString());

        return [$tasksByDate, $eventsByDate];
    }

    protected function dayItems(string $date): array
    {
        $day = CarbonImmutable::parse($date);
        $start = $day->startOfDay();
        $end = $day->endOfDay();

        $tasks = Task::forUser(Auth::id())
            ->whereBetween('due_at', [$start, $end])
            ->orderBy('due_at')
            ->get();

        $events = CalendarEvent::where('user_id', Auth::id())
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get();

        return [
            'day' => $day,
            'tasks' => $tasks,
            'events' => $events,
        ];
    }

    /* ===== CRUD событий ===== */

    public function openCreateEvent(?string $date = null): void
    {
        $this->editingEventId = null;
        $base = Carbon::parse($date ?? $this->selectedDate ?? now()->toDateString())->setTime(9, 0);
        $this->eventTitle = '';
        $this->eventDescription = '';
        $this->eventStartsAt = $base->format('Y-m-d\\TH:i');
        $this->eventEndsAt = $base->copy()->addHour()->format('Y-m-d\\TH:i');
        $this->eventAllDay = false;
        $this->eventColor = '#E5533A';
        $this->resetErrorBag();
        $this->showEventModal = true;
    }

    public function editEvent(int $id): void
    {
        $event = CalendarEvent::where('user_id', Auth::id())->findOrFail($id);

        $this->editingEventId = $event->id;
        $this->eventTitle = $event->title;
        $this->eventDescription = (string) $event->description;
        $this->eventStartsAt = $event->starts_at->format('Y-m-d\\TH:i');
        $this->eventEndsAt = $event->ends_at->format('Y-m-d\\TH:i');
        $this->eventAllDay = (bool) $event->all_day;
        $this->eventColor = $event->color ?: '#E5533A';
        $this->resetErrorBag();
        $this->showEventModal = true;
    }

    public function closeEventModal(): void
    {
        $this->showEventModal = false;
        $this->editingEventId = null;
    }

    public function saveEvent(): void
    {
        $this->validate();

        $payload = [
            'user_id' => Auth::id(),
            'title' => trim($this->eventTitle),
            'description' => $this->eventDescription !== '' ? $this->eventDescription : null,
            'starts_at' => Carbon::parse($this->eventStartsAt),
            'ends_at' => Carbon::parse($this->eventEndsAt),
            'all_day' => $this->eventAllDay,
            'color' => $this->eventColor ?: '#E5533A',
        ];

        if ($this->editingEventId) {
            CalendarEvent::where('user_id', Auth::id())
                ->findOrFail($this->editingEventId)
                ->update($payload);

            $this->dispatch('toast', type: 'success', title: 'Событие обновлено', message: $payload['title']);
        } else {
            CalendarEvent::create($payload);
            $this->dispatch('toast', type: 'success', title: 'Событие создано', message: $payload['title']);
        }

        $this->showEventModal = false;
        $this->editingEventId = null;
    }

    public function deleteEvent(int $id): void
    {
        $event = CalendarEvent::where('user_id', Auth::id())->findOrFail($id);
        $title = $event->title;
        $event->delete();

        $this->dispatch('toast', type: 'info', title: 'Событие удалено', message: $title);
    }
}
