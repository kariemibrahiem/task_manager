<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRelationRequest;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\MediaService;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly MediaService $mediaService) {}

    public function projectIndex(IndexRelationRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->indexResponse($request, $project);
    }

    public function projectStore(StoreMediaRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        return $this->storeResponse($request, $project);
    }

    public function taskIndex(IndexRelationRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task->project);

        return $this->indexResponse($request, $task);
    }

    public function taskStore(StoreMediaRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        return $this->storeResponse($request, $task);
    }

    public function commentIndex(IndexRelationRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('view', $comment);

        return $this->indexResponse($request, $comment);
    }

    public function commentStore(StoreMediaRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        return $this->storeResponse($request, $comment);
    }

    public function show(Media $media): JsonResponse
    {
        $this->authorize('view', $media);

        return $this->successResponse(new MediaResource($media), 'Media retrieved successfully.');
    }

    public function update(UpdateMediaRequest $request, Media $media): JsonResponse
    {
        $this->authorize('update', $media);

        return $this->successResponse(
            new MediaResource($this->mediaService->update($media, $request->validated())),
            'Media updated successfully.',
        );
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('delete', $media);
        $this->mediaService->delete($media);

        return $this->emptyResponse('Media deleted successfully.');
    }

    private function indexResponse(IndexRelationRequest $request, Model $model): JsonResponse
    {
        $media = $this->mediaService->paginate(
            $model,
            (int) $request->validated('per_page', 15),
        );

        return $this->paginatedResponse(MediaResource::collection($media), 'Media retrieved successfully.');
    }

    private function storeResponse(StoreMediaRequest $request, Model&HasMedia $model): JsonResponse
    {
        $media = $this->mediaService->create(
            $model,
            $request->file('file'),
            $request->validated('name'),
        );

        return $this->successResponse(
            new MediaResource($media),
            'Media uploaded successfully.',
            Response::HTTP_CREATED,
        );
    }
}
