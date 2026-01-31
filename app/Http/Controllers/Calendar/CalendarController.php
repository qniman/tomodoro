<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use App\Services\Calendar\CalendarService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(protected CalendarService $service) {}

    public function index(Request $request)
    {
        return CalendarEventResource::collection($this->service->list($request->user()));
    }

    public function store(CalendarEventRequest $request)
    {
        $event = $this->service->store($request->user(), $request->validated());

        return new CalendarEventResource($event);
    }

    public function update(CalendarEventRequest $request, CalendarEvent $event)
    {
        $this->ensureOwnership($event);

        return new CalendarEventResource($this->service->update($event, $request->validated()));
    }

    public function destroy(CalendarEvent $event)
    {
        $this->ensureOwnership($event);

        $this->service->delete($event);

        return response()->noContent();
    }

    protected function ensureOwnership(CalendarEvent $event): void
    {
        abort_unless(auth()->id() === $event->user_id, 403);
    }
}
