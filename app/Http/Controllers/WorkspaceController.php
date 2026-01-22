<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function tasks(): View
    {
        return view('todo.index');
    }

    public function timer(): View
    {
        return view('timer.index');
    }

    public function calendar(): View
    {
        return view('calendar.index');
    }

    public function apiDocs(): View
    {
        return view('api.docs');
    }

    public function presets(): View
    {
        return view('presets.index');
    }

    public function settings(): View
    {
        return view('settings.index');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme' => ['required', 'in:dark,light'],
        ]);

        $request->user()->update($data);
        session(['preferred_theme' => $data['theme']]);

        return back()->with('status', 'Настройки сохранены');
    }
}
