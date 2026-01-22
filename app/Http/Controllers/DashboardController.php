<?php

namespace App\Http\Controllers;

use App\Services\Calendar\CalendarService;
use App\Services\Todo\TaskService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(TaskService $taskService, CalendarService $calendarService): View
    {
        $user = auth()->user();
        $stats = [
            'tasks' => $user ? $taskService->listFor($user)->count() : 0,
            'calendar_events' => $user ? $calendarService->list($user)->count() : 0,
            'pomodoro_sessions' => $user ? $user->pomodoroSessions()->count() : 0,
        ];

        $activeSession = $user ? $user->pomodoroSessions()->where('status', 'running')->latest()->first() : null;
        $upcomingTasks = $user
            ? $user->tasks()->with('tags')->where('status', '!=', 'done')->orderByRaw('due_at is null, due_at asc')->limit(5)->get()
            : collect();
        $upcomingEvents = $user
            ? $calendarService->list($user)->sortBy('starts_at')->values()->take(5)
            : collect();
        $recentSessions = $user
            ? $user->pomodoroSessions()->latest()->limit(3)->get()
            : collect();

        return view('dashboard.index', compact('stats', 'activeSession', 'upcomingTasks', 'upcomingEvents', 'recentSessions'));
    }
}
