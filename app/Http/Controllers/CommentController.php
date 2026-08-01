<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRelationRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\CommentService;
use App\Traits\ApiTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly CommentService $comments) {}

    public function projectIndex(IndexRelationRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->indexResponse($request, $project);
    }

    public function projectStore(StoreCommentRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->storeResponse($request, $project);
    }

    public function taskIndex(IndexRelationRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task->project);

        return $this->indexResponse($request, $task);
    }

    public function taskStore(StoreCommentRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task->project);

        return $this->storeResponse($request, $task);
    }

    public function show(Comment $comment): JsonResponse
    {
        $this->authorize('view', $comment);

        return $this->successResponse(
            new CommentResource($comment->load(['user', 'media'])),
            'Comment retrieved successfully.',
        );
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        return $this->successResponse(
            new CommentResource($this->comments->update($comment, $request->validated())),
            'Comment updated successfully.',
        );
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $this->comments->delete($comment);

        return $this->emptyResponse('Comment deleted successfully.');
    }

    private function indexResponse(IndexRelationRequest $request, Model $commentable): JsonResponse
    {
        $comments = $this->comments->paginate(
            $commentable,
            (int) $request->validated('per_page', 15),
        );

        return $this->paginatedResponse(
            CommentResource::collection($comments),
            'Comments retrieved successfully.',
        );
    }

    private function storeResponse(StoreCommentRequest $request, Model $commentable): JsonResponse
    {
        $comment = $this->comments->create($commentable, $request->user(), $request->validated());

        return $this->successResponse(
            new CommentResource($comment),
            'Comment created successfully.',
            Response::HTTP_CREATED,
        );
    }
}
