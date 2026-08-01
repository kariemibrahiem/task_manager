<?php

namespace App\Contracts\Repositories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TagRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator;

    public function createForUser(User $user, array $attributes): Tag;

    public function update(Tag $tag, array $attributes): Tag;

    public function delete(Tag $tag): void;
}
