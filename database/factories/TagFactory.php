<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => Str::limit($this->faker->unique()->word().'-'.$this->faker->numerify('##'), 20, ''),
            'color' => sprintf('#%06x', $this->faker->numberBetween(0, 0xFFFFFF)),
            'icon' => null,
        ];
    }
}
