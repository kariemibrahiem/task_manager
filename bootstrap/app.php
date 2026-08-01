<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $errorResponse = static function (
            string $message,
            int $status,
            mixed $errors = null,
            array $data = [],
            array $headers = [],
        ): JsonResponse {
            $response = [
                'success' => false,
                'message' => $message,
                'data' => $data,
            ];

            if ($errors !== null) {
                $response['errors'] = $errors;
            }

            return response()->json($response, $status, $headers);
        };

        $exceptions->render(function (
            ValidationException $exception,
            Request $request,
        ) use ($errorResponse): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return $errorResponse(
                'The given data was invalid.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception->errors(),
            );
        });

        $exceptions->render(function (
            AuthenticationException $exception,
            Request $request,
        ) use ($errorResponse): ?JsonResponse {
            return $request->is('api/*')
                ? $errorResponse('Unauthenticated.', Response::HTTP_UNAUTHORIZED)
                : null;
        });

        $exceptions->render(function (
            AuthorizationException $exception,
            Request $request,
        ) use ($errorResponse): ?JsonResponse {
            return $request->is('api/*')
                ? $errorResponse(
                    $exception->getMessage() ?: 'This action is unauthorized.',
                    Response::HTTP_FORBIDDEN,
                )
                : null;
        });

        $exceptions->render(function (
            ThrottleRequestsException $exception,
            Request $request,
        ) use ($errorResponse): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            $headers = $exception->getHeaders();

            return $errorResponse(
                'Too many attempts. Please try again later.',
                Response::HTTP_TOO_MANY_REQUESTS,
                data: ['retry_after' => (int) ($headers['Retry-After'] ?? 60)],
                headers: $headers,
            );
        });

        $exceptions->render(function (
            Throwable $exception,
            Request $request,
        ) use ($errorResponse): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            $message = match ($status) {
                Response::HTTP_NOT_FOUND => 'Resource not found.',
                Response::HTTP_FORBIDDEN => $exception->getMessage() ?: 'This action is unauthorized.',
                Response::HTTP_METHOD_NOT_ALLOWED => 'Method not allowed.',
                Response::HTTP_INTERNAL_SERVER_ERROR => 'An unexpected error occurred.',
                default => Response::$statusTexts[$status] ?? 'Request failed.',
            };

            $headers = $exception instanceof HttpExceptionInterface
                ? $exception->getHeaders()
                : [];

            return $errorResponse($message, $status, headers: $headers);
        });
    })->create();
