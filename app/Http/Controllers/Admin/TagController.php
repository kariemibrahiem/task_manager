<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAdminResourceRequest;
use App\Http\Requests\Admin\UpdateAdminTagRequest;
use App\Http\Resources\Admin\AdminTagResource;
use App\Models\Tag;
use App\Services\AdminService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly AdminService $admin) {}

    public function index(IndexAdminResourceRequest $request): JsonResponse
    {
        return $this->paginatedResponse(
            AdminTagResource::collection($this->admin->tags(
                $request->safe()->only(['user_id', 'search']),
                $request->integer('per_page', 15),
            )),
            'Tags retrieved successfully.',
        );
    }

    public function show(Tag $tag): JsonResponse
    {
        return $this->successResponse(
            new AdminTagResource($tag->load('user')->loadCount(['projects', 'tasks'])),
            'Tag retrieved successfully.',
        );
    }

    public function update(UpdateAdminTagRequest $request, Tag $tag): JsonResponse
    {
        return $this->successResponse(
            new AdminTagResource($this->admin->updateTag($tag, $request->validated())),
            'Tag updated by administrator successfully.',
        );
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->admin->delete($tag);

        return $this->emptyResponse('Tag deleted by administrator successfully.');
    }
}
