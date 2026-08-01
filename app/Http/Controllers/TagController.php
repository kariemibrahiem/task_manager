<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRelationRequest;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Services\TagService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly TagService $tags) {}

    public function index(IndexRelationRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Tag::class);

        return $this->paginatedResponse(
            TagResource::collection($this->tags->paginate($request->user(), $request->integer('per_page', 15))),
            'Tags retrieved successfully.',
        );
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $this->authorize('create', Tag::class);

        return $this->successResponse(
            new TagResource($this->tags->create($request->user(), $request->validated())),
            'Tag created successfully.',
            Response::HTTP_CREATED,
        );
    }

    public function show(Tag $tag): JsonResponse
    {
        $this->authorize('view', $tag);

        return $this->successResponse(
            new TagResource($tag->loadCount(['projects', 'tasks'])),
            'Tag retrieved successfully.',
        );
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        return $this->successResponse(
            new TagResource($this->tags->update($tag, $request->validated())),
            'Tag updated successfully.',
        );
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);
        $this->tags->delete($tag);

        return $this->emptyResponse('Tag deleted successfully.');
    }

    public function attachToProject(Project $project, Tag $tag): JsonResponse
    {
        $this->authorize('update', $project);
        $this->authorize('view', $tag);

        return $this->successResponse(
            new ProjectResource($this->tags->attach($project, $tag)),
            'Tag attached to project successfully.',
        );
    }

    public function detachFromProject(Project $project, Tag $tag): JsonResponse
    {
        $this->authorize('update', $project);
        $this->authorize('view', $tag);

        return $this->successResponse(
            new ProjectResource($this->tags->detach($project, $tag)),
            'Tag detached from project successfully.',
        );
    }

    public function attachToTask(Task $task, Tag $tag): JsonResponse
    {
        $this->authorize('update', $task);
        $this->authorize('view', $tag);

        return $this->successResponse(
            new TaskResource($this->tags->attach($task, $tag)),
            'Tag attached to task successfully.',
        );
    }

    public function detachFromTask(Task $task, Tag $tag): JsonResponse
    {
        $this->authorize('update', $task);
        $this->authorize('view', $tag);

        return $this->successResponse(
            new TaskResource($this->tags->detach($task, $tag)),
            'Tag detached from task successfully.',
        );
    }
}
