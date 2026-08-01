<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $maxExceptions = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly string $uuid,
        public readonly ?int $userId,
        public readonly ?string $subjectType,
        public readonly int|string|null $subjectId,
        public readonly string $event,
        public readonly string $description,
        public readonly array $properties = [],
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}

    public function handle(): void
    {
        ActivityLog::firstOrCreate(
            ['uuid' => $this->uuid],
            [
                'user_id' => $this->userId,
                'subject_type' => $this->subjectType,
                'subject_id' => $this->subjectId,
                'event' => Str::limit($this->event, 50, ''),
                'description' => Str::limit($this->description, 255, ''),
                'properties' => $this->properties ?: null,
                'ip_address' => $this->ipAddress,
                'user_agent' => $this->userAgent,
            ],
        );
    }

    /** @return array<int, WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("activity-log:{$this->uuid}"))
                ->releaseAfter(5)
                ->expireAfter(120),
        ];
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [5, 15, 30, 60];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addMinutes(10);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Activity log job failed.', [
            'uuid' => $this->uuid,
            'event' => $this->event,
            'exception' => $exception->getMessage(),
        ]);
    }
}
