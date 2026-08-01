<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminDashboardResource;
use App\Services\AdminService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly AdminService $admin) {}

    public function __invoke(): JsonResponse
    {
        return $this->successResponse(
            new AdminDashboardResource($this->admin->dashboard()),
            'Administrator dashboard retrieved successfully.',
        );
    }
}
