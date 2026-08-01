<?php

namespace App\Services;

use App\Contracts\Repositories\MediaRepositoryInterface;
use App\Traits\ActivityLogTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    use ActivityLogTrait;

    public function __construct(private readonly MediaRepositoryInterface $media) {}

    public function paginate(Model $model, int $perPage): LengthAwarePaginator
    {
        return $this->media->paginateFor($model, $perPage);
    }

    public function create(Model&HasMedia $model, UploadedFile $file, ?string $name = null): Media
    {
        $adder = $model->addMedia($file);

        if (filled($name)) {
            $adder->usingName(trim($name));
        }

        $media = $adder->toMediaCollection('attachments');
        $this->logActivity('media_uploaded', 'Media uploaded.', $model, ['media_id' => $media->id]);

        return $media;
    }

    public function update(Media $media, array $attributes): Media
    {
        $media = $this->media->update($media, $attributes);
        $model = $media->model;

        if ($model instanceof Model) {
            $this->logActivity('media_updated', 'Media updated.', $model, ['media_id' => $media->id]);
        }

        return $media;
    }

    public function delete(Media $media): void
    {
        $model = $media->model;
        $mediaId = $media->id;
        $this->media->delete($media);

        if ($model instanceof Model) {
            $this->logActivity('media_deleted', 'Media deleted.', $model, ['media_id' => $mediaId]);
        }
    }
}
