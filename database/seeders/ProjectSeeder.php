<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use WithoutModelEvents;

    public const DEMO_EMAIL = 'demo@example.com';

    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo User',
                'password' => 'Password123',
                'email_verified_at' => now(),
            ],
        );

        foreach ($this->projects() as $attributes) {
            Project::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $attributes['name']],
                $attributes,
            );
        }
    }

    /** @return list<array{name: string, description: string, status: string}> */
    private function projects(): array
    {
        return [
            [
                'name' => 'Website Redesign',
                'description' => 'Redesign the public website and improve usability.',
                'status' => ProjectStatus::Active->value,
            ],
            [
                'name' => 'Mobile Application',
                'description' => 'Build the first version of the mobile application.',
                'status' => ProjectStatus::Active->value,
            ],
            [
                'name' => 'Legacy Migration',
                'description' => 'Migrate legacy project data to the new platform.',
                'status' => ProjectStatus::Completed->value,
            ],
        ];
    }
}
