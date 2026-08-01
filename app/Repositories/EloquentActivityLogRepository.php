<?php

namespace App\Repositories;

use App\Contracts\Repositories\ActivityLogRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function paginateForUser(User $user, array $filters, int $perPage): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->where('user_id', $user->id)
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->latest()
            ->paginate($perPage);
    }
}
