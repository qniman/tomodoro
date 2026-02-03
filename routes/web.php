<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tasks', [WorkspaceController::class, 'tasks'])->name('workspace.tasks');
    Route::get('/timer', [WorkspaceController::class, 'timer'])->name('workspace.timer');
    Route::get('/calendar', [WorkspaceController::class, 'calendar'])->name('workspace.calendar');
    Route::get('/api-playground', [WorkspaceController::class, 'apiDocs'])->name('workspace.api');
    Route::get('/presets', [WorkspaceController::class, 'presets'])->name('workspace.presets');
    Route::get('/settings', [WorkspaceController::class, 'settings'])->name('workspace.settings');

    Route::post('/settings', [WorkspaceController::class, 'updateSettings'])->name('workspace.settings.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
