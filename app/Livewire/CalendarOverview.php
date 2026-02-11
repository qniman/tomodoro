<?php

namespace App\Livewire;

use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\TaskCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class CalendarOverview extends Component
{
    public array $eventForm = [];
    public string $currentMonth;
    public bool $showEventModal = false;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->currentMonth = now()->startOfMonth()->toDateString();
        $this->resetEventForm();
    }

    public function render()
    {
        $month = Carbon::parse($this->currentMonth);
        [$calendarStart, $calendarEnd] = $this->calendarRange($month);

        $tasksByDate = Task::where('user_id', Auth::id())
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$calendarStart, $calendarEnd])
            ->get()
            ->groupBy(fn ($task) => $task->due_at->toDateString());

        $eventsByDate = CalendarEvent::where('user_id', Auth::id())
            ->whereBetween('starts_at', [$calendarStart, $calendarEnd])
            ->get()
            ->groupBy(fn ($event) => $event->starts_at->toDateString());

        $categoryPalette = TaskCategory::where('user_id', Auth::id())->pluck('color', 'name');

        $days = [];
        $cursor = $calendarStart->copy();
        while ($cursor->lte($calendarEnd)) {
            $dateKey = $cursor->toDateString();
            $days[] = [
                'date' => $cursor->copy(),
                'isCurrentMonth' => $cursor->month === $month->month,
                'tasks' => $tasksByDate->get($dateKey, collect()),
                'events' => $eventsByDate->get($dateKey, collect()),
            ];
            $cursor->addDay();
        }
        $weeks = array_chunk($days, 7);

        return view('livewire.calendar-overview', [
            'weeks' => $weeks,
            'monthLabel' => $month->locale('ru')->isoFormat('MMMM YYYY'),
            'events' => $this->eventsList(),
            'categoryPalette' => $categoryPalette,
        ]);
    }

    public function goToPreviousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->subMonth()->startOfMonth()->toDateString();
    }

    public function goToNextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)->addMonth()->startOfMonth()->toDateString();
    }

    public function openEventModal(?string $date = null): void
    {
        $this->selectedDate = $date;
        if ($date) {
            $start = Carbon::parse($date)->setTime(9, 0);
            $this->eventForm['starts_at'] = $start->format('Y-m-d\\TH:i');
            $this->eventForm['ends_at'] = $start->copy()->addHour()->format('Y-m-d\\TH:i');
        }
        $this->showEventModal = true;
    }

    public function createEvent(): void
    {
        $this->validate($this->rules());

        // Normalize datetime-local values
        if (! empty($this->eventForm['starts_at'])) {
            $this->eventForm['starts_at'] = Carbon::parse($this->eventForm['starts_at'])->toDateTimeString();
        }
        if (! empty($this->eventForm['ends_at'])) {
            $this->eventForm['ends_at'] = Carbon::parse($this->eventForm['ends_at'])->toDateTimeString();
        }

        CalendarEvent::create(array_merge($this->eventForm, [
            'user_id' => Auth::id(),
        ]));

        $this->resetEventForm();
        $this->showEventModal = false;
    }

    public function deleteEvent($id): void
    {
        CalendarEvent::where('user_id', Auth::id())->findOrFail($id)->delete();
    }

    protected function rules(): array
    {
        return [
            'eventForm.title' => ['required', 'string', 'max:255'],
            'eventForm.starts_at' => ['required', 'date'],
            'eventForm.ends_at' => ['required', 'date', 'after_or_equal:eventForm.starts_at'],
            'eventForm.color' => ['nullable', 'string'],
            'eventForm.description' => ['nullable', 'string'],
            'eventForm.task_id' => ['nullable', 'integer'],
        ];
    }

    protected function eventsList()
    {
        return CalendarEvent::where('user_id', Auth::id())
            ->orderBy('starts_at')
            ->limit(10)
            ->get();
    }

    protected function calendarRange(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth()->startOfWeek();
        $end = $month->copy()->endOfMonth()->endOfWeek();

        return [$start, $end];
    }

    protected function resetEventForm(): void
    {
        $this->eventForm = [
            'title' => '',
            'description' => '',
            'starts_at' => now()->format('Y-m-d\\TH:i'),
            'ends_at' => now()->addHour()->format('Y-m-d\\TH:i'),
            'color' => '#2563eb',
            'task_id' => null,
        ];
    }
}
