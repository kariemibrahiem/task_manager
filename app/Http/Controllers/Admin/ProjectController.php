<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAdminResourceRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\Admin\AdminProjectResource;
use App\Models\Project;
use App\Services\AdminService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly AdminService $admin) {}

    public function index(IndexAdminResourceRequest $request): JsonResponse
    {
        return $this->paginatedResponse(
            AdminProjectResource::collection($this->admin->projects(
                $request->safe()->only(['user_id', 'status', 'search']),
                $request->integer('per_page', 15),
            )),
            'Projects retrieved successfully.',
        );
    }

    public function show(Project $project): JsonResponse
    {
        return $this->successResponse(
            new AdminProjectResource($project->load(['user', 'tags'])->loadCount('tasks')),
            'Project retrieved successfully.',
        );
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        return $this->successResponse(
            new AdminProjectResource($this->admin->updateProject($project, $request->validated())),
            'Project updated by administrator successfully.',
        );
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->admin->delete($project);

        return $this->emptyResponse('Project deleted by administrator successfully.');
    }
}
