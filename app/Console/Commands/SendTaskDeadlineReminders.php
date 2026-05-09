<?php

namespace App\Console\Commands;

use App\Mail\TaskDeadlineMail;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaskDeadlineReminders extends Command
{
    protected $signature   = 'tomodoro:deadline-reminders';
    protected $description = 'Send email reminders for overdue, today and tomorrow tasks';

    public function handle(): int
    {
        $now      = Carbon::now();
        $today    = $now->copy()->startOfDay();
        $tomorrow = $now->copy()->addDay()->startOfDay();
        $dayAfter = $now->copy()->addDays(2)->startOfDay();

        $sentCount = 0;

        User::whereNotNull('email')->chunk(100, function ($users) use ($today, $tomorrow, $dayAfter, &$sentCount) {
            foreach ($users as $user) {
                $tasks = $this->buildTaskGroups($user, $today, $tomorrow, $dayAfter);

                $total = count($tasks['overdue']) + count($tasks['today']) + count($tasks['tomorrow']);
                if ($total === 0) {
                    continue;
                }

                Mail::to($user->email)->queue(new TaskDeadlineMail($user, $tasks));
                $sentCount++;
            }
        });

        $this->info("Sent {$sentCount} deadline reminder emails.");

        return self::SUCCESS;
    }

    private function buildTaskGroups(User $user, Carbon $today, Carbon $tomorrow, Carbon $dayAfter): array
    {
        $incomplete = Task::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->whereIn('priority', ['urgent', 'high', 'normal'])
            ->with('project')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
            ->get();

        $groups = ['overdue' => [], 'today' => [], 'tomorrow' => []];

        foreach ($incomplete as $task) {
            $due = Carbon::parse($task->due_at);

            $row = [
                'title'    => $task->title,
                'priority' => $task->priority,
                'project'  => $task->project?->name,
            ];

            if ($due->lt($today)) {
                $groups['overdue'][] = $row;
            } elseif ($due->lt($tomorrow)) {
                $groups['today'][] = $row;
            } elseif ($due->lt($dayAfter)) {
                $groups['tomorrow'][] = $row;
            }
        }

        // Cap lists to avoid enormous emails
        $groups['overdue']   = array_slice($groups['overdue'],   0, 10);
        $groups['today']     = array_slice($groups['today'],     0, 10);
        $groups['tomorrow']  = array_slice($groups['tomorrow'],  0, 10);

        return $groups;
    }
}
