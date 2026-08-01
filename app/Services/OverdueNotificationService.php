<?php

namespace App\Services;

use App\Contracts\Repositories\OverdueNotificationRepositoryInterface;
use App\Models\OverdueTaskNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OverdueNotificationService
{
    public function __construct(
        private readonly OverdueNotificationRepositoryInterface $notifications,
    ) {}

    public function listAndMarkAsSeen(User $user, int $perPage): LengthAwarePaginator
    {
        $notifications = $this->notifications->paginateForUser($user, $perPage);
        $this->notifications->markAsSeen($notifications->getCollection());

        return $notifications;
    }

    public function markAsSeen(OverdueTaskNotification $notification): OverdueTaskNotification
    {
        $this->notifications->markAsSeen(new Collection([$notification]));

        return $notification;
    }
}
