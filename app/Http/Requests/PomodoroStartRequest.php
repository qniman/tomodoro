<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PomodoroStartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'work_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'break_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'pomodoros' => ['nullable', 'integer', 'min:1', 'max:12'],
        ];
    }
}
