<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaRepositoryInterface
{
    public function paginateFor(Model $model, int $perPage): LengthAwarePaginator;

    public function update(Media $media, array $attributes): Media;

    public function delete(Media $media): void;
}
