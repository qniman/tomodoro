<?php

namespace Database\Factories;

use App\Models\PomodoroSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class PomodoroSessionFactory extends Factory
{
    protected $model = PomodoroSession::class;

    public function definition(): array
    {
        return [
            'work_sec' => 25 * 60,
            'break_sec' => 5 * 60,
            'total_pomodoros' => 4,
            'completed_pomodoros' => $this->faker->numberBetween(0, 4),
            'status' => $this->faker->randomElement(['running', 'finished', 'queued']),
            'started_at' => now()->subMinutes($this->faker->numberBetween(5, 120)),
            'ended_at' => now()->subMinutes($this->faker->numberBetween(-1, 5)),
        ];
    }
}
