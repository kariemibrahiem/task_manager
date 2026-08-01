<?php

namespace App\Repositories;

use App\Contracts\Repositories\OverdueNotificationRepositoryInterface;
use App\Models\OverdueTaskNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentOverdueNotificationRepository implements OverdueNotificationRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->overdueTaskNotifications()
            ->with('task:id,project_id,title,status,priority,due_date')
            ->latest()
            ->paginate($perPage);
    }

    public function markAsSeen(Collection $notifications): void
    {
        $unseenIds = $notifications
            ->where('seen', false)
            ->pluck('id');

        if ($unseenIds->isEmpty()) {
            return;
        }

        $seenAt = now();

        OverdueTaskNotification::query()
            ->whereIn('id', $unseenIds)
            ->update(['seen' => true, 'seen_at' => $seenAt]);

        $notifications->each(function (OverdueTaskNotification $notification) use ($seenAt): void {
            if (! $notification->seen) {
                $notification->setAttribute('seen', true);
                $notification->setAttribute('seen_at', $seenAt);
            }
        });
    }
}
