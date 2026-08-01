<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAdminResourceRequest;
use App\Http\Requests\Admin\UpdateUserAccessRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\User;
use App\Services\AdminService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly AdminService $admin) {}

    public function index(IndexAdminResourceRequest $request): JsonResponse
    {
        return $this->paginatedResponse(
            AdminUserResource::collection($this->admin->users(
                $request->safe()->only(['role', 'status', 'search']),
                $request->integer('per_page', 15),
            )),
            'Users retrieved successfully.',
        );
    }

    public function show(User $user): JsonResponse
    {
        return $this->successResponse(
            new AdminUserResource($user->loadCount(['projects', 'tags'])),
            'User retrieved successfully.',
        );
    }

    public function update(UpdateUserAccessRequest $request, User $user): JsonResponse
    {
        return $this->successResponse(
            new AdminUserResource($this->admin->updateUser($request->user(), $user, $request->validated())),
            'User access updated successfully.',
        );
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->admin->deleteUser($request->user(), $user);

        return $this->emptyResponse('User deleted successfully.');
    }
}
