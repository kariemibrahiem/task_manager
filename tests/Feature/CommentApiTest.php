<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_comments_on_own_project_and_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->for($project)->create();
        Sanctum::actingAs($user);

        $projectComment = $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'body' => 'Project comment',
        ])->assertCreated()->json('data.id');

        $taskComment = $this->postJson("/api/v1/tasks/{$task->id}/comments", [
            'body' => 'Task comment',
        ])->assertCreated()->json('data.id');

        $this->getJson("/api/v1/projects/{$project->id}/comments")
            ->assertOk()
            ->assertJsonCount(1, 'data.items');
        $this->getJson("/api/v1/tasks/{$task->id}/comments")
            ->assertOk()
            ->assertJsonCount(1, 'data.items');

        $this->getJson("/api/v1/comments/{$taskComment}")
            ->assertOk()
            ->assertJsonPath('data.body', 'Task comment');

        $this->patchJson("/api/v1/comments/{$taskComment}", ['body' => 'Updated task comment'])
            ->assertOk()
            ->assertJsonPath('data.body', 'Updated task comment');

        $this->deleteJson("/api/v1/comments/{$projectComment}")
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->assertSoftDeleted('comments', ['id' => $projectComment]);
    }

    public function test_comments_are_included_in_project_and_task_views(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->for($project)->create();
        Comment::factory()->for($user)->for($project, 'commentable')->create();
        Comment::factory()->for($user)->for($task, 'commentable')->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.comments')
            ->assertJsonCount(1, 'data.tasks.0.comments');

        $this->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.comments');
    }

    public function test_user_cannot_access_or_modify_another_users_comments(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $comment = Comment::factory()->for($project->user)->for($project, 'commentable')->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/comments/{$comment->id}")->assertForbidden();
        $this->patchJson("/api/v1/comments/{$comment->id}", ['body' => 'Forbidden'])->assertForbidden();
        $this->deleteJson("/api/v1/comments/{$comment->id}")->assertForbidden();
    }
}
