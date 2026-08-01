<?php

namespace App\Jobs;

use Database\Seeders\ActivityLogSeeder;
use Database\Seeders\CommentSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\TaskSeeder;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SeedSampleDataJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $uniqueFor = 300;

    public function handle(
        ProjectSeeder $projects,
        TaskSeeder $tasks,
        CommentSeeder $comments,
        ActivityLogSeeder $activityLogs,
    ): void {
        $projects->run();
        $tasks->run();
        $comments->run();
        $activityLogs->run();
    }

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('sample-data-seeding'))
                ->releaseAfter(10)
                ->expireAfter(300),
        ];
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function uniqueId(): string
    {
        return 'task-management-sample-data';
    }
}
