<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::query()->where('email', ProjectSeeder::DEMO_EMAIL)->firstOrFail();

        foreach ($user->projects()->with('tasks')->get()->pluck('tasks')->flatten() as $task) {
            Comment::query()->firstOrCreate([
                'user_id' => $user->id,
                'commentable_type' => $task->getMorphClass(),
                'commentable_id' => $task->id,
                'body' => 'Sample comment for this task.',
            ]);
        }
    }
}
