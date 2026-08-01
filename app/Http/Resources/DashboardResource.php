<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /** @return array<string, int> */
    public function toArray(Request $request): array
    {
        return [
            'total_projects' => (int) $this['total_projects'],
            'active_projects' => (int) $this['active_projects'],
            'total_tasks' => (int) $this['total_tasks'],
            'completed_tasks' => (int) $this['completed_tasks'],
            'pending_tasks' => (int) $this['pending_tasks'],
            'overdue_tasks' => (int) $this['overdue_tasks'],
        ];
    }
}
