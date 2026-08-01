<?php

namespace App\Contracts\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator;

    public function createForUser(User $user, array $attributes): Project;

    public function update(Project $project, array $attributes): Project;

    public function delete(Project $project): void;
}
