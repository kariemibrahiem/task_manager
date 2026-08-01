<?php

namespace App\Services;

use App\Contracts\Repositories\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\User;
use App\Traits\ActivityLogTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommentService
{
    use ActivityLogTrait;

    public function __construct(private readonly CommentRepositoryInterface $comments) {}

    public function paginate(Model $commentable, int $perPage): LengthAwarePaginator
    {
        return $this->comments->paginateFor($commentable, $perPage);
    }

    public function create(Model $commentable, User $user, array $attributes): Comment
    {
        return DB::transaction(function () use ($commentable, $user, $attributes): Comment {
            $comment = $this->comments->createFor($commentable, $user, $attributes);
            $this->logActivity('created', 'Comment created.', $comment);

            return $comment->load(['user', 'media']);
        });
    }

    public function update(Comment $comment, array $attributes): Comment
    {
        return DB::transaction(function () use ($comment, $attributes): Comment {
            $comment = $this->comments->update($comment, $attributes);
            $this->logActivity('updated', 'Comment updated.', $comment);

            return $comment->load(['user', 'media']);
        });
    }

    public function delete(Comment $comment): void
    {
        DB::transaction(function () use ($comment): void {
            $this->logActivity('deleted', 'Comment deleted.', $comment);
            $this->comments->delete($comment);
        });
    }
}
