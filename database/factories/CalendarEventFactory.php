<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('now', '+3 weeks');
        $end = (clone $start)->modify('+1 hour');

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'starts_at' => $start,
            'ends_at' => $end,
            'color' => sprintf('#%06x', $this->faker->numberBetween(0, 0xffffff)),
        ];
    }
}
