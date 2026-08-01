<?php

namespace App\Services;

use App\Contracts\Repositories\TagRepositoryInterface;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Traits\ActivityLogTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagService
{
    use ActivityLogTrait;

    public function __construct(private readonly TagRepositoryInterface $tags) {}

    public function paginate(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->tags->paginateForUser($user, $perPage);
    }

    public function create(User $user, array $attributes): Tag
    {
        $attributes['name'] = trim($attributes['name']);
        $attributes['slug'] = $this->slug($attributes['name']);

        return $this->tags->createForUser($user, $attributes);
    }

    public function update(Tag $tag, array $attributes): Tag
    {
        if (isset($attributes['name'])) {
            $attributes['name'] = trim($attributes['name']);
            $attributes['slug'] = $this->slug($attributes['name']);
        }

        return $this->tags->update($tag, $attributes);
    }

    public function delete(Tag $tag): void
    {
        DB::transaction(function () use ($tag): void {
            $tag->projects()->detach();
            $tag->tasks()->detach();
            $this->tags->delete($tag);
        });
    }

    public function attach(Project|Task $taggable, Tag $tag): Model
    {
        $taggable->tags()->syncWithoutDetaching([$tag->id]);
        $this->logActivity('tag_attached', 'Tag attached.', $taggable, ['tag_id' => $tag->id]);

        return $taggable->load('tags');
    }

    public function detach(Project|Task $taggable, Tag $tag): Model
    {
        $taggable->tags()->detach($tag->id);
        $this->logActivity('tag_detached', 'Tag detached.', $taggable, ['tag_id' => $tag->id]);

        return $taggable->load('tags');
    }

    private function slug(string $name): string
    {
        return Str::slug($name) ?: Str::lower(Str::random(12));
    }
}
