<?php

namespace App\Repositories;

use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminRepository implements AdminRepositoryInterface
{
    public function dashboard(): array
    {
        return [
            'total_users' => User::query()->count(),
            'active_users' => User::query()->where('status', UserStatus::Active->value)->count(),
            'suspended_users' => User::query()->where('status', UserStatus::Suspended->value)->count(),
            'total_projects' => Project::query()->count(),
            'active_projects' => Project::query()->where('status', ProjectStatus::Active->value)->count(),
            'total_tasks' => Task::query()->count(),
            'completed_tasks' => Task::query()->where('status', TaskStatus::Done->value)->count(),
            'overdue_tasks' => Task::query()->where('status', '!=', TaskStatus::Done->value)
                ->whereNotNull('due_date')->where('due_date', '<', now())->count(),
            'total_tags' => Tag::query()->count(),
        ];
    }

    public function paginateUsers(array $filters, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->withCount(['projects', 'tags'])
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(
                fn ($query) => $query->where('name', 'like', $this->like($search))
                    ->orWhere('email', 'like', $this->like($search)),
            ))
            ->latest()->paginate($perPage);
    }

    public function paginateProjects(array $filters, int $perPage): LengthAwarePaginator
    {
        return Project::query()->with(['user', 'tags'])->withCount('tasks')
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', $this->like($search)))
            ->latest()->paginate($perPage);
    }

    public function paginateTasks(array $filters, int $perPage): LengthAwarePaginator
    {
        return Task::query()->with(['project.user', 'tags'])
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->whereHas('project', fn ($query) => $query->where('user_id', $id)))
            ->when($filters['project_id'] ?? null, fn ($query, $id) => $query->where('project_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->when(isset($filters['overdue']), fn ($query) => filter_var($filters['overdue'], FILTER_VALIDATE_BOOLEAN)
                ? $query->where('status', '!=', TaskStatus::Done->value)->whereNotNull('due_date')->where('due_date', '<', now())
                : $query->where(fn ($query) => $query->whereNull('due_date')->orWhere('due_date', '>=', now())->orWhere('status', TaskStatus::Done->value)))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', $this->like($search)))
            ->latest()->paginate($perPage);
    }

    public function paginateTags(array $filters, int $perPage): LengthAwarePaginator
    {
        return Tag::query()->with('user')->withCount(['projects', 'tasks'])
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', $this->like($search)))
            ->latest()->paginate($perPage);
    }

    public function updateUser(User $user, array $attributes): User
    {
        $user->forceFill($attributes)->save();

        return $user->refresh()->loadCount(['projects', 'tags']);
    }

    public function updateProject(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project->refresh()->load(['user', 'tags'])->loadCount('tasks');
    }

    public function updateTask(Task $task, array $attributes): Task
    {
        $task->update($attributes);

        return $task->refresh()->load(['project.user', 'tags']);
    }

    public function updateTag(Tag $tag, array $attributes): Tag
    {
        $tag->update($attributes);

        return $tag->refresh()->load('user')->loadCount(['projects', 'tasks']);
    }

    public function delete(User|Project|Task|Tag $model): void
    {
        $model->delete();
    }

    private function like(string $search): string
    {
        return '%'.addcslashes($search, '%_\\').'%';
    }
}
