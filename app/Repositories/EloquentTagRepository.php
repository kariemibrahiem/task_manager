<?php

namespace App\Repositories;

use App\Contracts\Repositories\TagRepositoryInterface;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTagRepository implements TagRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->tags()->withCount(['projects', 'tasks'])->latest()->paginate($perPage);
    }

    public function createForUser(User $user, array $attributes): Tag
    {
        return $user->tags()->create($attributes);
    }

    public function update(Tag $tag, array $attributes): Tag
    {
        $tag->update($attributes);

        return $tag->refresh()->loadCount(['projects', 'tasks']);
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}
