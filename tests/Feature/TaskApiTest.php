<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_update_and_delete_task_in_own_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $created = $this->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Finish assessment',
            'priority' => TaskPriority::High->value,
            'status' => TaskStatus::Todo->value,
            'due_date' => now()->addDay()->toISOString(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.priority', TaskPriority::High->value);

        $taskId = $created->json('data.id');

        $this->patchJson("/api/v1/tasks/{$taskId}", [
            'status' => TaskStatus::Done->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', TaskStatus::Done->value)
            ->assertJsonPath('data.completed_at', fn ($value) => $value !== null);

        $this->deleteJson("/api/v1/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->assertSoftDeleted('tasks', ['id' => $taskId]);
    }

    public function test_task_list_supports_status_priority_search_and_pagination(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Sanctum::actingAs($user);

        Task::factory()->for($project)->create([
            'title' => 'Important Laravel report',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
        ]);
        Task::factory()->for($project)->create([
            'title' => 'Unrelated task',
            'status' => TaskStatus::Todo->value,
            'priority' => TaskPriority::Low->value,
        ]);

        $query = http_build_query([
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
            'search' => 'Laravel',
            'per_page' => 1,
        ]);

        $response = $this->getJson("/api/v1/projects/{$project->id}/tasks?{$query}")
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Important Laravel report')
            ->assertJsonPath('data.pagination.per_page', 1);

        $this->assertSame([
            'total',
            'current_page',
            'per_page',
            'next_page',
            'prev_page',
            'from',
            'last_page_url',
        ], array_keys($response->json('data.pagination')));
    }

    public function test_user_cannot_manage_tasks_in_another_users_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->for($project)->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/projects/{$project->id}/tasks", ['title' => 'Forbidden'])
            ->assertForbidden();
        $this->patchJson("/api/v1/tasks/{$task->id}", ['status' => TaskStatus::Done->value])
            ->assertForbidden();
        $this->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertForbidden();
    }

    public function test_task_priority_and_status_must_use_enum_values(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Invalid task',
            'priority' => 'normal',
            'status' => 'inprogress',
        ])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['priority', 'status']]);
    }
}
