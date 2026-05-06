<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Workspace\CalendarView;
use App\Livewire\Workspace\Settings;
use App\Livewire\Workspace\TaskBoard;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/app');

    // Задачи: одна реализация для разных «пресетов» — Сегодня / Входящие / Предстоящие / Все
    Route::get('/app', TaskBoard::class)->name('app');
    Route::get('/app/today', TaskBoard::class)->defaults('scope', 'today')->name('app.today');
    Route::get('/app/inbox', TaskBoard::class)->defaults('scope', 'inbox')->name('app.inbox');
    Route::get('/app/upcoming', TaskBoard::class)->defaults('scope', 'upcoming')->name('app.upcoming');
    Route::get('/app/all', TaskBoard::class)->defaults('scope', 'all')->name('app.all');

    Route::get('/app/calendar', CalendarView::class)->name('app.calendar');
    Route::get('/app/settings', Settings::class)->name('app.settings');

    Route::post('/logout', LogoutController::class)->name('logout');
});
