<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_and_paginate_own_tags(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $tagId = $this->postJson('/api/v1/tags', [
            'name' => 'Urgent',
            'color' => '#FF0000',
        ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'urgent')
            ->json('data.id');

        $this->getJson('/api/v1/tags?per_page=10')
            ->assertOk()
            ->assertJsonCount(1, 'data.items');

        $this->patchJson("/api/v1/tags/{$tagId}", ['name' => 'Critical'])
            ->assertOk()
            ->assertJsonPath('data.slug', 'critical');

        $this->deleteJson("/api/v1/tags/{$tagId}")
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->assertSoftDeleted('tags', ['id' => $tagId]);
    }

    public function test_user_can_attach_and_detach_a_tag_from_own_project_and_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->for($project)->create();
        $tag = Tag::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/projects/{$project->id}/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonPath('data.tags.0.id', $tag->id);

        $this->putJson("/api/v1/tasks/{$task->id}/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonPath('data.tags.0.id', $tag->id);

        $this->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('data.tags.0.id', $tag->id)
            ->assertJsonPath('data.tasks.0.tags.0.id', $tag->id);

        $this->deleteJson("/api/v1/projects/{$project->id}/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data.tags');

        $this->deleteJson("/api/v1/tasks/{$task->id}/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data.tags');
    }

    public function test_user_cannot_access_or_attach_another_users_tag(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $otherTag = Tag::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/tags/{$otherTag->id}")->assertForbidden();
        $this->putJson("/api/v1/projects/{$project->id}/tags/{$otherTag->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'This tag does not belong to your account.');

        $this->assertDatabaseMissing('taggables', ['tag_id' => $otherTag->id]);
    }

    public function test_tag_name_is_unique_per_user_and_color_must_be_hex(): void
    {
        $user = User::factory()->create();
        Tag::factory()->for($user)->create(['name' => 'Backend']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tags', ['name' => 'Backend'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['name']]);

        $this->postJson('/api/v1/tags', ['name' => 'Frontend', 'color' => 'red'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['color']]);
    }
}
