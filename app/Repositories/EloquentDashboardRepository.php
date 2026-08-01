<?php

namespace App\Repositories;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\OverdueTaskNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function statisticsForUser(User $user): array
    {
        $projects = Project::query()
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) as total_projects')
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_projects',
                [ProjectStatus::Active->value],
            )
            ->first();

        $tasks = Task::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', $user->id))
            ->selectRaw('COUNT(*) as total_tasks')
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_tasks',
                [TaskStatus::Done->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as pending_tasks',
                [TaskStatus::Done->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN due_date < ? AND status != ? THEN 1 ELSE 0 END) as overdue_tasks',
                [now(), TaskStatus::Done->value],
            )
            ->first();

        return [
            'total_projects' => (int) $projects->total_projects,
            'active_projects' => (int) $projects->active_projects,
            'total_tasks' => (int) $tasks->total_tasks,
            'completed_tasks' => (int) $tasks->completed_tasks,
            'pending_tasks' => (int) $tasks->pending_tasks,
            'overdue_tasks' => (int) $tasks->overdue_tasks,
            'unread_notifications' => OverdueTaskNotification::query()
                ->where('user_id', $user->id)
                ->where('seen', false)
                ->count(),
        ];
    }
}
