<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PomodoroSessionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'work_sec' => $this->work_sec,
            'break_sec' => $this->break_sec,
            'total_pomodoros' => $this->total_pomodoros,
            'completed_pomodoros' => $this->completed_pomodoros,
            'status' => $this->status,
            'started_at' => optional($this->started_at)->toDateTimeString(),
            'ended_at' => optional($this->ended_at)->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
