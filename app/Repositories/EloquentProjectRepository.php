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
            ->withCount('tasks')
            ->latest()
            ->paginate($perPage);
    }

    public function createForUser(User $user, array $attributes): Project
    {
        return $user->projects()->create($attributes);
    }

    public function update(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
