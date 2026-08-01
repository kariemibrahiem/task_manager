<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\OverdueTaskNotification;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CreateOverdueTaskNotificationsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 3600;

    public function handle(): void
    {
        Task::query()
            ->with('project:id,user_id')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereHas('project')
            ->chunkById(200, function ($tasks): void {
                $now = now();
                $rows = $tasks->map(fn (Task $task): array => [
                    'user_id' => $task->project->user_id,
                    'task_id' => $task->id,
                    'message' => "Task '{$task->title}' is overdue.",
                    'seen' => false,
                    'seen_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                OverdueTaskNotification::query()->insertOrIgnore($rows);
            });
    }

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('overdue-task-notifications'))
                ->releaseAfter(30)
                ->expireAfter(300),
        ];
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function uniqueId(): string
    {
        return 'overdue-task-notifications';
    }
}
