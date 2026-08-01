<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly ProjectService $projects) {}

    public function index(IndexProjectRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);
        $projects = $this->projects->paginate(
            $request->user(),
            (int) $request->validated('per_page', 15),
        );

        return $this->paginatedResponse(
            ProjectResource::collection($projects),
            'Projects retrieved successfully.',
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);
        $project = $this->projects->create($request->user(), $request->validated());

        return $this->successResponse(
            new ProjectResource($project),
            'Project created successfully.',
            Response::HTTP_CREATED,
        );
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->successResponse(
            new ProjectResource($project->load([
                'comments.user',
                'comments.media',
                'media',
                'tags',
                'tasks.comments.user',
                'tasks.comments.media',
                'tasks.media',
                'tasks.tags',
            ])->loadCount('tasks')),
            'Project retrieved successfully.',
        );
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project = $this->projects->update($project, $request->validated());

        return $this->successResponse(
            new ProjectResource($project),
            'Project updated successfully.',
        );
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $this->projects->delete($project);

        return $this->emptyResponse('Project deleted successfully.');
    }
}
