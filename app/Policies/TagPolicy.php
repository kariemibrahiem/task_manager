<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tag $tag): Response
    {
        return $this->owns($user, $tag);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tag $tag): Response
    {
        return $this->owns($user, $tag);
    }

    public function delete(User $user, Tag $tag): Response
    {
        return $this->owns($user, $tag);
    }

    private function owns(User $user, Tag $tag): Response
    {
        return $tag->user_id === $user->id
            ? Response::allow()
            : Response::deny('This tag does not belong to your account.');
    }
}
