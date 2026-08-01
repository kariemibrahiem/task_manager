<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActivityLogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_list_filter_and_view_own_activity_logs(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $ownLog = ActivityLog::factory()->for($user)->for($project, 'subject')->create(['event' => 'updated']);
        ActivityLog::factory()->create(['event' => 'updated']);
        ActivityLog::factory()->for($user)->for($project, 'subject')->create(['event' => 'created']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/activity-logs?event=updated&per_page=10')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $ownLog->id);

        $this->getJson("/api/v1/activity-logs/{$ownLog->id}")
            ->assertOk()
            ->assertJsonPath('data.subject_type', 'Project');
    }

    public function test_user_cannot_view_another_users_activity_log(): void
    {
        $user = User::factory()->create();
        $activityLog = ActivityLog::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/activity-logs/{$activityLog->id}")->assertForbidden();
    }

    public function test_activity_logs_are_not_publicly_mutable(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/activity-logs', [])->assertMethodNotAllowed();
    }
}
