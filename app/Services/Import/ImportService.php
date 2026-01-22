<?php

namespace App\Services\Import;

use App\Models\User;

class ImportService
{
    public function importTasks(User $user, string $payload, string $format = 'json'): array
    {
        $rows = $format === 'csv' ? $this->parseCsv($payload) : json_decode($payload, true);
        $created = [];

        foreach ($rows as $row) {
            $data = [
                'title' => $row['title'] ?? 'Untitled',
                'description' => $row['description'] ?? null,
                'category' => $row['category'] ?? null,
                'priority' => $row['priority'] ?? 'medium',
                'status' => $row['status'] ?? 'pending',
                'due_at' => $row['due_at'] ?? null,
            ];

            $task = $user->tasks()->create($data);
            $created[] = $task->title;
        }

        return $created;
    }

    protected function parseCsv(string $payload): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $payload)));

        if (empty($lines)) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        $rows = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);
            $rows[] = array_combine($headers, $values);
        }

        return $rows;
    }
}
