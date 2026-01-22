<?php

namespace App\Services\Calendar;

use App\Models\CalendarEvent;
use App\Models\User;

class CalendarService
{
    public function list(User $user)
    {
        return $user->calendarEvents()->orderBy('starts_at')->get();
    }

    public function store(User $user, array $data): CalendarEvent
    {
        return $user->calendarEvents()->create($data);
    }

    public function update(CalendarEvent $event, array $data): CalendarEvent
    {
        $event->update($data);

        return $event;
    }

    public function delete(CalendarEvent $event): void
    {
        $event->delete();
    }
}
