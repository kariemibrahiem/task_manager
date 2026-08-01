<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_list_view_update_and_delete_own_project(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $created = $this->postJson('/api/v1/projects', [
            'name' => 'Assessment API',
            'description' => 'Task management project.',
            'status' => ProjectStatus::Active->value,
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Assessment API');

        $projectId = $created->json('data.id');
        Task::factory()->for(Project::query()->findOrFail($projectId))->count(2)->create();

        $this->getJson('/api/v1/projects?per_page=10')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', $projectId)
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'pagination' => [
                        'total',
                        'current_page',
                        'per_page',
                        'next_page',
                        'prev_page',
                        'from',
                        'last_page_url',
                    ],
                ],
            ])
            ->assertJsonMissingPath('data.links');

        $this->getJson("/api/v1/projects/{$projectId}")
            ->assertOk()
            ->assertJsonPath('data.id', $projectId)
            ->assertJsonPath('data.tasks_count', 2)
            ->assertJsonCount(2, 'data.tasks');

        $this->patchJson("/api/v1/projects/{$projectId}", [
            'status' => ProjectStatus::Completed->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', ProjectStatus::Completed->value);

        $this->deleteJson("/api/v1/projects/{$projectId}")
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->assertSoftDeleted('projects', ['id' => $projectId]);
    }

    public function test_user_cannot_access_another_users_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/projects/{$project->id}")
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', []);
    }

    public function test_project_list_only_contains_authenticated_users_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->for($user)->count(2)->create();
        Project::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(2, 'data.items');
    }

    public function test_project_status_must_be_a_supported_enum_value(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/projects', [
            'name' => 'Invalid Project',
            'status' => 'pending',
        ])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['status']]);
    }
}
