<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user()?->status !== UserStatus::Active) {
            return new JsonResponse([
                'success' => false,
                'message' => 'This account is suspended.',
                'data' => [],
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
