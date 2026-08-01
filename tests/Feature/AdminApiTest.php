<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_administrator_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/admin/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Administrator access is required.');
    }

    public function test_admin_dashboard_returns_platform_wide_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        $activeUser = User::factory()->create();
        User::factory()->suspended()->create();
        $project = Project::factory()->for($activeUser)->create(['status' => ProjectStatus::Active]);
        Task::factory()->for($project)->create([
            'status' => TaskStatus::Todo,
            'due_date' => now()->subHour(),
        ]);
        Tag::factory()->for($activeUser)->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.total_users', 3)
            ->assertJsonPath('data.active_users', 2)
            ->assertJsonPath('data.suspended_users', 1)
            ->assertJsonPath('data.total_projects', 1)
            ->assertJsonPath('data.overdue_tasks', 1)
            ->assertJsonPath('data.total_tags', 1);
    }

    public function test_admin_can_manage_users_without_removing_own_access(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $user->createToken('test');
        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/admin/users/{$user->id}", [
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ])->assertOk()->assertJsonPath('data.role', 'admin');

        $this->patchJson("/api/v1/admin/users/{$user->id}", [
            'status' => UserStatus::Suspended->value,
        ])->assertOk()->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->patchJson("/api/v1/admin/users/{$admin->id}", ['role' => UserRole::User->value])
            ->assertUnprocessable();
        $this->deleteJson("/api/v1/admin/users/{$admin->id}")
            ->assertUnprocessable();
    }

    public function test_admin_can_filter_and_manage_all_projects_tasks_and_tags(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $project = Project::factory()->for($owner)->create();
        $task = Task::factory()->for($project)->create();
        $tag = Tag::factory()->for($owner)->create();
        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/admin/projects?user_id={$owner->id}")
            ->assertOk()->assertJsonPath('data.items.0.owner.id', $owner->id);
        $this->patchJson("/api/v1/admin/projects/{$project->id}", ['status' => ProjectStatus::Archived->value])
            ->assertOk()->assertJsonPath('data.status', 'archived');

        $this->getJson('/api/v1/admin/tasks?overdue=0')
            ->assertOk()->assertJsonPath('data.items.0.id', $task->id);
        $this->patchJson("/api/v1/admin/tasks/{$task->id}", [
            'status' => TaskStatus::Done->value,
            'priority' => TaskPriority::High->value,
        ])->assertOk()->assertJsonPath('data.status', 'done');

        $this->getJson("/api/v1/admin/tags?user_id={$owner->id}")
            ->assertOk()->assertJsonPath('data.items.0.owner.id', $owner->id);
        $this->patchJson("/api/v1/admin/tags/{$tag->id}", ['name' => 'Reviewed'])
            ->assertOk()->assertJsonPath('data.slug', 'reviewed');
    }

    public function test_suspended_user_cannot_login_or_use_an_existing_token(): void
    {
        $user = User::factory()->suspended()->create(['password' => 'password']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'This account is suspended.');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test',
        ])->assertUnauthorized();
    }

    public function test_admin_create_command_creates_an_active_admin(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'System Admin',
            '--email' => 'admin@example.com',
            '--password' => 'StrongPassword123!',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);
    }
}
