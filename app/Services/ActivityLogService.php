<?php

namespace App\Services;

use App\Contracts\Repositories\ActivityLogRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    public function __construct(private readonly ActivityLogRepositoryInterface $activityLogs) {}

    public function paginate(User $user, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->activityLogs->paginateForUser($user, $filters, $perPage);
    }
}
