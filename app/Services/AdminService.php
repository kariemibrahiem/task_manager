<?php

namespace App\Services;

use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Traits\ActivityLogTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminService
{
    use ActivityLogTrait;

    public function __construct(private readonly AdminRepositoryInterface $admin) {}

    public function dashboard(): array
    {
        return $this->admin->dashboard();
    }

    public function users(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->admin->paginateUsers($filters, $perPage);
    }

    public function projects(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->admin->paginateProjects($filters, $perPage);
    }

    public function tasks(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->admin->paginateTasks($filters, $perPage);
    }

    public function tags(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->admin->paginateTags($filters, $perPage);
    }

    public function updateUser(User $actor, User $user, array $attributes): User
    {
        $this->guardAdminAccessChange($actor, $user, $attributes);

        return DB::transaction(function () use ($user, $attributes): User {
            $updated = $this->admin->updateUser($user, $attributes);
            $this->logActivity('admin_user_updated', 'User access updated by administrator.', $updated, $attributes);

            if (($attributes['status'] ?? null) === UserStatus::Suspended->value) {
                $updated->tokens()->delete();
            }

            return $updated;
        });
    }

    public function updateProject(Project $project, array $attributes): Project
    {
        return $this->updateModel($project, $attributes, 'admin_project_updated');
    }

    public function updateTask(Task $task, array $attributes): Task
    {
        if (array_key_exists('status', $attributes)) {
            $attributes['completed_at'] = $attributes['status'] === TaskStatus::Done->value
                ? ($task->completed_at ?? now())
                : null;
        }

        return $this->updateModel($task, $attributes, 'admin_task_updated');
    }

    public function updateTag(Tag $tag, array $attributes): Tag
    {
        if (isset($attributes['name'])) {
            $attributes['name'] = trim($attributes['name']);
            $attributes['slug'] = Str::slug($attributes['name']) ?: Str::lower(Str::random(12));
        }

        return $this->updateModel($tag, $attributes, 'admin_tag_updated');
    }

    public function deleteUser(User $actor, User $user): void
    {
        $this->guardAdminAccessChange($actor, $user, ['role' => UserRole::User->value]);

        DB::transaction(function () use ($user): void {
            $this->logActivity('admin_user_deleted', 'User deleted by administrator.', $user);
            $user->tokens()->delete();
            $this->admin->delete($user);
        });
    }

    public function delete(Project|Task|Tag $model): void
    {
        DB::transaction(function () use ($model): void {
            if ($model instanceof Tag) {
                $model->projects()->detach();
                $model->tasks()->detach();
            }

            $this->logActivity('admin_'.Str::snake(class_basename($model)).'_deleted', 'Resource deleted by administrator.', $model);
            $this->admin->delete($model);
        });
    }

    private function updateModel(Project|Task|Tag $model, array $attributes, string $event): Project|Task|Tag
    {
        return DB::transaction(function () use ($model, $attributes, $event): Project|Task|Tag {
            $updated = match (true) {
                $model instanceof Project => $this->admin->updateProject($model, $attributes),
                $model instanceof Task => $this->admin->updateTask($model, $attributes),
                default => $this->admin->updateTag($model, $attributes),
            };
            $this->logActivity($event, 'Resource updated by administrator.', $updated, $attributes);

            return $updated;
        });
    }

    private function guardAdminAccessChange(User $actor, User $user, array $attributes): void
    {
        $removesAdminAccess = ($attributes['role'] ?? $user->role->value) !== UserRole::Admin->value
            || ($attributes['status'] ?? $user->status->value) !== UserStatus::Active->value;

        if ($actor->is($user) && $removesAdminAccess) {
            throw ValidationException::withMessages(['user' => 'You cannot remove your own administrator access.']);
        }

        if ($user->isAdmin() && $removesAdminAccess && User::query()->where('role', UserRole::Admin->value)->count() <= 1) {
            throw ValidationException::withMessages(['user' => 'The last administrator cannot be demoted, suspended, or deleted.']);
        }
    }
}
