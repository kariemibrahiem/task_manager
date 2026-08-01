<?php

namespace App\Contracts\Repositories;

use App\Models\OverdueTaskNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OverdueNotificationRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator;

    /** @param Collection<int, OverdueTaskNotification> $notifications */
    public function markAsSeen(Collection $notifications): void;
}
