<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexRelationRequest;
use App\Http\Resources\OverdueTaskNotificationResource;
use App\Models\OverdueTaskNotification;
use App\Services\OverdueNotificationService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class OverdueNotificationController extends Controller
{
    use ApiTrait;

    public function __construct(
        private readonly OverdueNotificationService $notifications,
    ) {}

    public function index(IndexRelationRequest $request): JsonResponse
    {
        $notifications = $this->notifications->listAndMarkAsSeen(
            $request->user(),
            $request->integer('per_page', 15),
        );

        return $this->paginatedResponse(
            OverdueTaskNotificationResource::collection($notifications),
            'Notifications retrieved and marked as seen successfully.',
        );
    }

    public function show(OverdueTaskNotification $notification): JsonResponse
    {
        $this->authorize('view', $notification);
        $notification->load('task:id,project_id,title,status,priority,due_date');

        return $this->successResponse(
            new OverdueTaskNotificationResource($this->notifications->markAsSeen($notification)),
            'Notification retrieved and marked as seen successfully.',
        );
    }
}
