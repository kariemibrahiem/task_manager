<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAdminResourceRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\Admin\AdminTaskResource;
use App\Models\Task;
use App\Services\AdminService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly AdminService $admin) {}

    public function index(IndexAdminResourceRequest $request): JsonResponse
    {
        return $this->paginatedResponse(
            AdminTaskResource::collection($this->admin->tasks(
                $request->safe()->only(['user_id', 'project_id', 'status', 'priority', 'overdue', 'search']),
                $request->integer('per_page', 15),
            )),
            'Tasks retrieved successfully.',
        );
    }

    public function show(Task $task): JsonResponse
    {
        return $this->successResponse(
            new AdminTaskResource($task->load(['project.user', 'tags'])),
            'Task retrieved successfully.',
        );
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        return $this->successResponse(
            new AdminTaskResource($this->admin->updateTask($task, $request->validated())),
            'Task updated by administrator successfully.',
        );
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->admin->delete($task);

        return $this->emptyResponse('Task deleted by administrator successfully.');
    }
}
