<?php

namespace App\Trait;

trait HttpResponse
{
    protected function success($data, $message = null, $code = 200)
    {
        return new \Illuminate\Http\JsonResponse([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($data, $message = null, $code = 400)
    {
        return new \Illuminate\Http\JsonResponse([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function withPagination($collection, ?string $message = null, $code = 200, ?array $extraMeta = [])
    {
        return new \Illuminate\Http\JsonResponse([
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
