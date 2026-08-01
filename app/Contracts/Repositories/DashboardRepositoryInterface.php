<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface DashboardRepositoryInterface
{
    /** @return array<string, int> */
    public function statisticsForUser(User $user): array;
}
