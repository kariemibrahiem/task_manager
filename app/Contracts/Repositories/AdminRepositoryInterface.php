<?php

namespace App\Contracts\Repositories;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminRepositoryInterface
{
    public function dashboard(): array;

    public function paginateUsers(array $filters, int $perPage): LengthAwarePaginator;

    public function paginateProjects(array $filters, int $perPage): LengthAwarePaginator;

    public function paginateTasks(array $filters, int $perPage): LengthAwarePaginator;

    public function paginateTags(array $filters, int $perPage): LengthAwarePaginator;

    public function updateUser(User $user, array $attributes): User;

    public function updateProject(Project $project, array $attributes): Project;

    public function updateTask(Task $task, array $attributes): Task;

    public function updateTag(Tag $tag, array $attributes): Tag;

    public function delete(User|Project|Task|Tag $model): void;
}
