<?php

namespace App\Trait;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait HttpResponse
{
    protected function success(mixed $data, ?string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(mixed $data, ?string $message = null, int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function withPagination($collection, ?string $message = null, int $code = Response::HTTP_OK, ?array $extraMeta = []): JsonResponse
    {
        return new JsonResponse([
            'status' => true,
            'message' => $message,
            'data' => $collection->items(),
            'pagination' => [
                'current_page' => $collection->currentPage(),
                'last_page' => $collection->lastPage(),
                'per_page' => $collection->perPage(),
                'total' => $collection->total(),
                'prev_page_url' => $collection->previousPageUrl(),
                'next_page_url' => $collection->nextPageUrl(),
            ],
            'meta' => $extraMeta,
        ], $code);
    }
}
