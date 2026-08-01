<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly TaskService $tasks) {}

    public function index(IndexTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('viewAny', [Task::class, $project]);
        $tasks = $this->tasks->paginate(
            $project,
            $request->safe()->only(['status', 'priority', 'search']),
            (int) $request->validated('per_page', 15),
        );

        return $this->paginatedResponse(
            TaskResource::collection($tasks),
            'Tasks retrieved successfully.',
        );
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);
        $task = $this->tasks->create($project, $request->validated());

        return $this->successResponse(
            new TaskResource($task),
            'Task created successfully.',
            Response::HTTP_CREATED,
        );
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task->project);

        return $this->successResponse(
            new TaskResource($task->load(['comments.user', 'comments.media', 'media', 'tags'])),
            'Task retrieved successfully.',
        );
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $task = $this->tasks->update($task, $request->validated());

        return $this->successResponse(
            new TaskResource($task),
            'Task updated successfully.',
        );
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $this->tasks->delete($task);

        return $this->emptyResponse('Task deleted successfully.');
    }
}
