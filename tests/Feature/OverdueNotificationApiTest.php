<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Jobs\CreateOverdueTaskNotificationsJob;
use App\Models\OverdueTaskNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OverdueNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_one_notification_for_each_overdue_task_without_duplicates(): void
    {
        $project = Project::factory()->create();
        $overdueTask = Task::factory()->for($project)->create([
            'status' => TaskStatus::Todo,
            'due_date' => now()->subHour(),
        ]);
        Task::factory()->for($project)->create(['due_date' => now()->addHour()]);
        Task::factory()->for($project)->create([
            'status' => TaskStatus::Done,
            'due_date' => now()->subHour(),
        ]);

        $job = new CreateOverdueTaskNotificationsJob;
        $job->handle();
        $job->handle();

        $this->assertDatabaseCount('overdue_task_notifications', 1);
        $this->assertDatabaseHas('overdue_task_notifications', [
            'user_id' => $project->user_id,
            'task_id' => $overdueTask->id,
            'seen' => false,
        ]);
    }

    public function test_listing_notifications_marks_only_the_users_returned_page_as_seen(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        OverdueTaskNotification::query()->insert([
            $this->notificationData($user, Task::factory()->for($project)->create()),
            $this->notificationData($user, Task::factory()->for($project)->create()),
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications?per_page=1')
            ->assertOk()
            ->assertJsonPath('data.items.0.seen', 1)
            ->assertJsonPath('data.pagination.total', 2);

        $this->assertDatabaseCount('overdue_task_notifications', 2);
        $this->assertSame(1, OverdueTaskNotification::query()->where('seen', true)->count());
    }

    public function test_user_can_view_own_notification_but_not_another_users_notification(): void
    {
        $user = User::factory()->create();
        $ownProject = Project::factory()->for($user)->create();
        $ownNotification = OverdueTaskNotification::query()->create(
            $this->notificationData($user, Task::factory()->for($ownProject)->create()),
        );

        $otherProject = Project::factory()->create();
        $otherNotification = OverdueTaskNotification::query()->create(
            $this->notificationData($otherProject->user, Task::factory()->for($otherProject)->create()),
        );
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/notifications/{$ownNotification->id}")
            ->assertOk()
            ->assertJsonPath('data.seen', 1);

        $this->getJson("/api/v1/notifications/{$otherNotification->id}")
            ->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function notificationData(User $user, Task $task): array
    {
        return [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'message' => "Task '{$task->title}' is overdue.",
            'seen' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
