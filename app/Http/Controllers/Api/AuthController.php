<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiTrait;

    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $auth = $this->authService->register(
            $request->safe()->only(['name', 'email', 'password']),
            $request->string('device_name')->value(),
        );

        return $this->successResponse([
            'user' => new UserResource($auth['user']),
            'token' => $auth['token'],
        ], 'Registration completed successfully.', Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $auth = $this->authService->login(
            $request->string('email')->value(),
            $request->string('password')->value(),
            $request->string('device_name')->value(),
        );

        if ($auth === null) {
            return $this->errorResponse(
                'The provided credentials are incorrect.',
                Response::HTTP_UNAUTHORIZED,
            );
        }

        return $this->successResponse([
            'user' => new UserResource($auth['user']),
            'token' => $auth['token'],
        ], 'Login completed successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->emptyResponse('Logout completed successfully.');
    }
}
