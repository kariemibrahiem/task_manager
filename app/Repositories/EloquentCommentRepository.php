<?php

namespace App\Repositories;

use App\Contracts\Repositories\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function paginateFor(Model $commentable, int $perPage): LengthAwarePaginator
    {
        return $commentable->comments()
            ->with(['user', 'media'])
            ->latest()
            ->paginate($perPage);
    }

    public function createFor(Model $commentable, User $user, array $attributes): Comment
    {
        return $commentable->comments()->create([
            'user_id' => $user->id,
            ...$attributes,
        ]);
    }

    public function update(Comment $comment, array $attributes): Comment
    {
        $comment->update($attributes);

        return $comment->refresh();
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
