<?php

namespace App\Contracts\Repositories;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function paginateForProject(Project $project, array $filters, int $perPage): LengthAwarePaginator;

    public function createForProject(Project $project, array $attributes): Task;

    public function update(Task $task, array $attributes): Task;

    public function delete(Task $task): void;
}
