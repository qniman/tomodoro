<?php

namespace App\Services\Todo;

use App\Models\TaskCategory;
use App\Models\TaskStatus;
use App\Models\User;

class TaskPresetService
{
    public function seedDefaultsFor(User $user): void
    {
        // Ensure the allowed statuses exist for the user. Do not create arbitrary statuses.
        foreach (TaskStatus::ALLOWED_STATUSES as $status) {
            TaskStatus::firstOrCreate(
                ['user_id' => $user->id, 'name' => $status['name']],
                ['color' => $status['color']]
            );
        }

        if (! $user->taskCategories()->exists()) {
            $categories = [
                ['name' => 'Работа', 'color' => '#6366f1'],
                ['name' => 'Личное', 'color' => '#f472b6'],
                ['name' => 'Учёба', 'color' => '#fb923c'],
            ];

            foreach ($categories as $category) {
                TaskCategory::create([
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'color' => $category['color'],
                ]);
            }
        }
    }
}
