<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        return $this->ownsModel($user, $media);
    }

    public function update(User $user, Media $media): bool
    {
        if ($media->model instanceof Comment) {
            return $media->model->user_id === $user->id && $this->ownsModel($user, $media);
        }

        return $this->ownsModel($user, $media);
    }

    public function delete(User $user, Media $media): bool
    {
        return $this->update($user, $media);
    }

    private function ownsModel(User $user, Media $media): bool
    {
        return match (true) {
            $media->model instanceof Project => $media->model->user_id === $user->id,
            $media->model instanceof Task => $media->model->project->user_id === $user->id,
            $media->model instanceof Comment => $this->ownsCommentable($user, $media->model),
            default => false,
        };
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
