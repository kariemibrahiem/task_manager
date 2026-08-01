<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_only_authenticated_users_statistics(): void
    {
        $user = User::factory()->create();
        $activeProject = Project::factory()->for($user)->create(['status' => ProjectStatus::Active->value]);
        $completedProject = Project::factory()->for($user)->create(['status' => ProjectStatus::Completed->value]);

        Task::factory()->for($activeProject)->create([
            'status' => TaskStatus::Todo->value,
            'due_date' => now()->subDay(),
        ]);
        Task::factory()->for($activeProject)->create([
            'status' => TaskStatus::InProgress->value,
            'due_date' => now()->addDay(),
        ]);
        Task::factory()->for($completedProject)->create([
            'status' => TaskStatus::Done->value,
            'due_date' => now()->subDay(),
            'completed_at' => now(),
        ]);

        $otherProject = Project::factory()->create(['status' => ProjectStatus::Active->value]);
        Task::factory()->for($otherProject)->create([
            'status' => TaskStatus::Todo->value,
            'due_date' => now()->subDay(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_projects', 2)
            ->assertJsonPath('data.active_projects', 1)
            ->assertJsonPath('data.total_tasks', 3)
            ->assertJsonPath('data.completed_tasks', 1)
            ->assertJsonPath('data.pending_tasks', 2)
            ->assertJsonPath('data.overdue_tasks', 1);
    }
}
