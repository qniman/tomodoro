<?php

namespace App\Http\Controllers\Timer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PomodoroStartRequest;
use App\Http\Resources\PomodoroSessionResource;
use App\Models\PomodoroSession;
use App\Services\Pomodoro\PomodoroService;
use Illuminate\Http\Request;

class PomodoroController extends Controller
{
    public function __construct(protected PomodoroService $service) {}

    public function index(Request $request)
    {
        return PomodoroSessionResource::collection(
            $request->user()->pomodoroSessions()->latest()->get()
        );
    }

    public function start(PomodoroStartRequest $request)
    {
        $session = $this->service->start($request->user(), $request->validated());

        return new PomodoroSessionResource($session);
    }

    public function stop(PomodoroSession $session)
    {
        $this->ensureOwnership($session);

        return new PomodoroSessionResource($this->service->stop($session));
    }

    public function complete(PomodoroSession $session)
    {
        $this->ensureOwnership($session);

        return new PomodoroSessionResource($this->service->completePomodoro($session));
    }

    protected function ensureOwnership(PomodoroSession $session): void
    {
        abort_unless(auth()->id() === $session->user_id, 403);
    }
}
