<?php

namespace App\Policies;

use App\Models\OverdueTaskNotification;
use App\Models\User;

class OverdueTaskNotificationPolicy
{
    public function view(User $user, OverdueTaskNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
