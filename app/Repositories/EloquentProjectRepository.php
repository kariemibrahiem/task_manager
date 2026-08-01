<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->projects()
            ->with('tags')
            ->withCount('tasks')
            ->latest()
            ->paginate($perPage);
    }

    public function createForUser(User $user, array $attributes): Project
    {
        return $user->projects()->create($attributes)->load('tags');
    }

    public function update(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project->refresh()->load('tags');
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
