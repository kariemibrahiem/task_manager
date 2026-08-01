<?php

namespace App\Traits;

use App\Jobs\ActivityLogJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait ActivityLogTrait
{
    protected function logActivity(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = [],
    ): void {
        $hasRequest = app()->bound('request');

        ActivityLogJob::dispatch(
            uuid: (string) Str::uuid(),
            userId: auth()->id(),
            subjectType: $subject?->getMorphClass(),
            subjectId: $subject?->getKey(),
            event: $event,
            description: $description,
            properties: $properties,
            ipAddress: $hasRequest ? request()->ip() : null,
            userAgent: $hasRequest ? request()->userAgent() : null,
        )
            ->onQueue('activity-logs')
            ->afterCommit();
    }
}
