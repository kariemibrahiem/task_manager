<?php

namespace App\Repositories;

use App\Contracts\Repositories\MediaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EloquentMediaRepository implements MediaRepositoryInterface
{
    public function paginateFor(Model $model, int $perPage): LengthAwarePaginator
    {
        return $model->media()
            ->latest()
            ->paginate($perPage);
    }

    public function update(Media $media, array $attributes): Media
    {
        $media->update($attributes);

        return $media->refresh();
    }

    public function delete(Media $media): void
    {
        $media->delete();
    }
}
