<?php

namespace App\Support\Media;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class ModelPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->basePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->basePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->basePath($media).'/responsive-images/';
    }

    private function basePath(Media $media): string
    {
        $directory = match ($media->model_type) {
            Project::class => 'projects',
            Task::class => 'tasks',
            Comment::class => 'comments',
            default => 'media',
        };

        return "{$directory}/{$media->model_id}/{$media->getKey()}";
    }
}
