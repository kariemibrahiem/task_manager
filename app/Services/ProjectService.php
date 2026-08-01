<?php

namespace App\Services;

use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Models\Project;
use App\Models\User;
use App\Traits\ActivityLogTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    use ActivityLogTrait;

    public function __construct(private readonly ProjectRepositoryInterface $projects) {}

    public function paginate(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->projects->paginateForUser($user, $perPage);
    }

    public function create(User $user, array $attributes): Project
    {
        return DB::transaction(function () use ($user, $attributes): Project {
            $project = $this->projects->createForUser($user, $attributes);
            $this->logActivity('created', 'Project created.', $project, ['attributes' => $project->toArray()]);

            return $project;
        });
    }

    public function update(Project $project, array $attributes): Project
    {
        return DB::transaction(function () use ($project, $attributes): Project {
            $before = $project->only(array_keys($attributes));
            $project = $this->projects->update($project, $attributes);
            $this->logActivity('updated', 'Project updated.', $project, [
                'before' => $before,
                'after' => $project->only(array_keys($attributes)),
            ]);

            return $project;
        });
    }

    public function delete(Project $project): void
    {
        DB::transaction(function () use ($project): void {
            $this->logActivity('deleted', 'Project deleted.', $project);
            $this->projects->delete($project);
        });
    }
}
