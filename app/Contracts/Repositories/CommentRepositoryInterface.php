<?php

namespace App\Contracts\Repositories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface CommentRepositoryInterface
{
    public function paginateFor(Model $commentable, int $perPage): LengthAwarePaginator;

    public function createFor(Model $commentable, User $user, array $attributes): Comment;

    public function update(Comment $comment, array $attributes): Comment;

    public function delete(Comment $comment): void;
}
