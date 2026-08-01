<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexActivityLogRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function index(IndexActivityLogRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ActivityLog::class);
        $activityLogs = $this->activityLogs->paginate(
            $request->user(),
            $request->safe()->only(['event']),
            (int) $request->validated('per_page', 15),
        );

        return $this->paginatedResponse(
            ActivityLogResource::collection($activityLogs),
            'Activity logs retrieved successfully.',
        );
    }

    public function show(ActivityLog $activityLog): JsonResponse
    {
        $this->authorize('view', $activityLog);

        return $this->successResponse(
            new ActivityLogResource($activityLog),
            'Activity log retrieved successfully.',
        );
    }
}
