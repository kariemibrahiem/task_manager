<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_list_view_rename_and_delete_project_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $mediaId = $this->post("/api/v1/projects/{$project->id}/media", [
            'file' => UploadedFile::fake()->create('requirements.pdf', 100, 'application/pdf'),
            'name' => 'Requirements',
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Requirements')
            ->json('data.id');

        $this->getJson("/api/v1/projects/{$project->id}/media")
            ->assertOk()
            ->assertJsonCount(1, 'data.items');
        $this->getJson("/api/v1/media/{$mediaId}")
            ->assertOk()
            ->assertJsonPath('data.file_name', 'requirements.pdf');
        $this->patchJson("/api/v1/media/{$mediaId}", ['name' => 'Updated requirements'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated requirements');
        $this->deleteJson("/api/v1/media/{$mediaId}")
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    }

    public function test_media_can_be_attached_to_tasks_and_comments(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->for($project)->create();
        $comment = Comment::factory()->for($user)->for($task, 'commentable')->create();
        Sanctum::actingAs($user);

        $this->post("/api/v1/tasks/{$task->id}/media", [
            'file' => UploadedFile::fake()->create('task.txt', 10, 'text/plain'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $this->post("/api/v1/comments/{$comment->id}/media", [
            'file' => UploadedFile::fake()->create('comment.txt', 10, 'text/plain'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $this->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.media')
            ->assertJsonCount(1, 'data.comments.0.media');

        $taskMedia = Media::query()->whereMorphedTo('model', $task)->firstOrFail();
        $commentMedia = Media::query()->whereMorphedTo('model', $comment)->firstOrFail();

        Storage::disk('public')->assertExists("tasks/{$task->id}/{$taskMedia->id}/task.txt");
        Storage::disk('public')->assertExists("comments/{$comment->id}/{$commentMedia->id}/comment.txt");
    }

    public function test_project_media_is_stored_in_its_own_directory(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $mediaId = $this->post("/api/v1/projects/{$project->id}/media", [
            'file' => UploadedFile::fake()->image('cover.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->json('data.id');

        Storage::disk('public')->assertExists("projects/{$project->id}/{$mediaId}/cover.jpg");
    }

    public function test_unsafe_media_type_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->post("/api/v1/projects/{$project->id}/media", [
            'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['file']]);
    }

    public function test_user_cannot_access_another_users_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $media = $project->addMedia(UploadedFile::fake()->create('private.pdf', 10, 'application/pdf'))
            ->toMediaCollection('attachments');
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/media/{$media->id}")->assertForbidden();
        $this->deleteJson("/api/v1/media/{$media->id}")->assertForbidden();
    }
}
