<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function paginateForProject(Project $project, array $filters, int $perPage): LengthAwarePaginator
    {
        return $project->tasks()
            ->with('tags')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('title', 'like', '%'.addcslashes($search, '%_\\').'%');
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createForProject(Project $project, array $attributes): Task
    {
        return $project->tasks()->create($attributes)->load('tags');
    }

    public function update(Task $task, array $attributes): Task
    {
        $task->update($attributes);

        return $task->refresh()->load('tags');
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
