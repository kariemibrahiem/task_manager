<?php

namespace App\Services;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Models\User;

class DashboardService
{
    public function __construct(private readonly DashboardRepositoryInterface $dashboard) {}

    /** @return array<string, int> */
    public function statistics(User $user): array
    {
        return $this->dashboard->statisticsForUser($user);
    }
}
