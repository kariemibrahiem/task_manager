<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

trait ApiTrait
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Request completed successfully.',
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? [],
        ], $status);
    }

    protected function errorResponse(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $errors = null,
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => [],
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function emptyResponse(
        string $message = 'Request completed successfully.',
    ): JsonResponse {
        return $this->successResponse([], $message);
    }

    protected function paginatedResponse(
        AnonymousResourceCollection $collection,
        string $message = 'Resources retrieved successfully.',
    ): JsonResponse {
        $payload = $collection->response()->getData(true);

        return $this->successResponse([
            'items' => $payload['data'],
            'pagination' => [
                'total' => $payload['meta']['total'],
                'current_page' => $payload['meta']['current_page'],
                'per_page' => $payload['meta']['per_page'],
                'next_page' => $payload['links']['next'],
                'prev_page' => $payload['links']['prev'],
                'from' => $payload['meta']['from'],
                'last_page_url' => $payload['links']['last'],
            ],
        ], $message);
    }
}
