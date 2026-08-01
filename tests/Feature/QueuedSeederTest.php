<?php

namespace Tests\Feature;

use App\Jobs\SeedSampleDataJob;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\ActivityLogSeeder;
use Database\Seeders\CommentSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\TaskSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueuedSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_dispatches_idempotent_sample_data_job(): void
    {
        Queue::fake();

        $this->seed(DatabaseSeeder::class);

        Queue::assertPushedOn('seeding', SeedSampleDataJob::class);

        $job = new SeedSampleDataJob;
        $seeders = [
            app(ProjectSeeder::class),
            app(TaskSeeder::class),
            app(CommentSeeder::class),
            app(ActivityLogSeeder::class),
        ];

        $job->handle(...$seeders);
        $job->handle(...$seeders);

        $this->assertSame(1, User::query()->where('email', ProjectSeeder::DEMO_EMAIL)->count());
        $this->assertSame(3, Project::query()->count());
        $this->assertSame(9, Task::query()->count());
        $this->assertSame(9, Comment::query()->count());
        $this->assertSame(9, ActivityLog::query()->where('event', 'seeded')->count());
    }
}
