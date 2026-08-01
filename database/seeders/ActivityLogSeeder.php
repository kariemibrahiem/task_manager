<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActivityLogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::query()->where('email', ProjectSeeder::DEMO_EMAIL)->firstOrFail();

        foreach ($user->projects()->with('tasks')->get()->pluck('tasks')->flatten() as $task) {
            ActivityLog::query()->firstOrCreate(
                [
                    'subject_type' => $task->getMorphClass(),
                    'subject_id' => $task->id,
                    'event' => 'seeded',
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'description' => 'Sample task created by the queued seeder.',
                    'properties' => ['source' => 'seeder'],
                ],
            );
        }
    }
}
