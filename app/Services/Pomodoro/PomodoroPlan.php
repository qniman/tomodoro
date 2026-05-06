<?php

namespace App\Services\Pomodoro;

/**
 * Read-only объект плана: что и сколько помодоро запускать.
 */
final class PomodoroPlan
{
    public function __construct(
        public readonly int $workMinutes,
        public readonly int $shortBreakMinutes,
        public readonly int $longBreakMinutes,
        public readonly int $longBreakEvery,
        public readonly int $totalPomodoros,
        public readonly string $source = 'default', // estimate | deadline | fallback | default
    ) {
    }

    public function totalFocusMinutes(): int
    {
        return $this->workMinutes * $this->totalPomodoros;
    }

    public function toArray(): array
    {
        return [
            'work_minutes' => $this->workMinutes,
            'short_break_minutes' => $this->shortBreakMinutes,
            'long_break_minutes' => $this->longBreakMinutes,
            'long_break_every' => $this->longBreakEvery,
            'total_pomodoros' => $this->totalPomodoros,
            'source' => $this->source,
        ];
    }
}
