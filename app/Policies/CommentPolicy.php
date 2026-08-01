<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function view(User $user, Comment $comment): bool
    {
        return $this->ownsCommentable($user, $comment);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id && $this->ownsCommentable($user, $comment);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }

    private function ownsCommentable(User $user, Comment $comment): bool
    {
        return match (true) {
            $comment->commentable instanceof Project => $comment->commentable->user_id === $user->id,
            $comment->commentable instanceof Task => $comment->commentable->project->user_id === $user->id,
            default => false,
        };
    }
}
