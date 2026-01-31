<?php

namespace App\Services\Export;

use App\Models\User;

class ExportService
{
    public function exportTasks(User $user, string $format = 'json'): string
    {
        $collection = $user->tasks()->with('tags')->get()->map(function ($task) {
            return [
                'title' => $task->title,
                'description' => $task->description,
                'category' => $task->category,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_at' => optional($task->due_at)->toDateTimeString(),
                'tags' => $task->tags->pluck('name')->all(),
            ];
        });

        if ($format === 'csv') {
            return $this->toCsv($collection->toArray());
        }

        return $collection->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    protected function toCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $headers = array_keys(reset($rows));
        $lines = [implode(',', $headers)];

        foreach ($rows as $row) {
            $lines[] = implode(',', array_map([$this, 'encodeCsvValue'], $row));
        }

        return implode("\n", $lines);
    }

    protected function encodeCsvValue(mixed $value): string
    {
        $value = is_array($value) ? implode('|', $value) : (string) $value;

        if (str_contains($value, ',')) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }
}
