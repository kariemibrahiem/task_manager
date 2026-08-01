<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::query()->where('email', ProjectSeeder::DEMO_EMAIL)->firstOrFail();

        foreach ($user->projects as $project) {
            foreach ($this->tasks($project) as $attributes) {
                Task::query()->updateOrCreate(
                    ['project_id' => $project->id, 'title' => $attributes['title']],
                    $attributes,
                );
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function tasks(Project $project): array
    {
        return [
            [
                'title' => "Plan {$project->name}",
                'description' => 'Define scope, milestones, and acceptance criteria.',
                'priority' => TaskPriority::High->value,
                'status' => TaskStatus::InProgress->value,
                'due_date' => now()->addDays(7),
                'completed_at' => null,
            ],
            [
                'title' => "Review {$project->name}",
                'description' => 'Review the completed work with the project owner.',
                'priority' => TaskPriority::Medium->value,
                'status' => TaskStatus::Todo->value,
                'due_date' => now()->addDays(14),
                'completed_at' => null,
            ],
            [
                'title' => "Document {$project->name}",
                'description' => 'Write concise technical and user documentation.',
                'priority' => TaskPriority::Low->value,
                'status' => TaskStatus::Done->value,
                'due_date' => now()->subDay(),
                'completed_at' => now(),
            ],
        ];
    }
}
