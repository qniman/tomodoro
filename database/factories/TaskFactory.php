<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $priorities = ['low', 'medium', 'high'];
        $statuses = ['pending', 'in_progress', 'done'];

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['Работа', 'Личное', 'Учёба']),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $this->faker->randomElement($statuses),
            'due_at' => $this->faker->dateTimeBetween('now', '+2 weeks'),
            'est_minutes' => $this->faker->numberBetween(15, 120),
            'actual_minutes' => $this->faker->numberBetween(0, 120),
        ];
    }
}
