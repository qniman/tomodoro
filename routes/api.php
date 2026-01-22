<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\ExportImportController;
use App\Http\Controllers\Timer\PomodoroController;
use App\Http\Controllers\Todo\TagController;
use App\Http\Controllers\Todo\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('tags', TagController::class)->except(['create', 'edit']);

    Route::get('pomodoro/sessions', [PomodoroController::class, 'index']);
    Route::post('pomodoro/start', [PomodoroController::class, 'start']);
    Route::post('pomodoro/stop/{session}', [PomodoroController::class, 'stop']);
    Route::post('pomodoro/complete/{session}', [PomodoroController::class, 'complete']);

    Route::apiResource('calendar/events', CalendarController::class);

    Route::post('export', [ExportImportController::class, 'export']);
    Route::post('import', [ExportImportController::class, 'import']);
});
