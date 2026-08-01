<?php

namespace App\Services;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Traits\ActivityLogTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskService
{
    use ActivityLogTrait;

    public function __construct(private readonly TaskRepositoryInterface $tasks) {}

    public function paginate(Project $project, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->tasks->paginateForProject($project, $filters, $perPage);
    }

    public function create(Project $project, array $attributes): Task
    {
        return DB::transaction(function () use ($project, $attributes): Task {
            $attributes = $this->synchronizeCompletionDate($attributes);
            $task = $this->tasks->createForProject($project, $attributes);
            $this->logActivity('created', 'Task created.', $task, ['attributes' => $task->toArray()]);

            return $task;
        });
    }

    public function update(Task $task, array $attributes): Task
    {
        return DB::transaction(function () use ($task, $attributes): Task {
            $before = $task->only(array_keys($attributes));
            $attributes = $this->synchronizeCompletionDate($attributes, $task);
            $task = $this->tasks->update($task, $attributes);
            $this->logActivity('updated', 'Task updated.', $task, [
                'before' => $before,
                'after' => $task->only(array_keys($attributes)),
            ]);

            return $task;
        });
    }

    public function delete(Task $task): void
    {
        DB::transaction(function () use ($task): void {
            $this->logActivity('deleted', 'Task deleted.', $task);
            $this->tasks->delete($task);
        });
    }

    private function synchronizeCompletionDate(array $attributes, ?Task $task = null): array
    {
        $status = $attributes['status'] ?? $task?->status?->value ?? TaskStatus::Todo->value;

        $attributes['completed_at'] = $status === TaskStatus::Done->value
            ? ($task?->completed_at ?? now())
            : null;

        return $attributes;
    }
}
