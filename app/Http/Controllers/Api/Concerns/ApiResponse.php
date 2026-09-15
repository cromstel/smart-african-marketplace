<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Every /api/v1 controller was building its own `response()->json([...])`
 * array by hand. Most agreed on {success, data, message}, but several
 * `paginate()` calls had their pagination metadata (current_page, total,
 * next page url, ...) silently discarded because the paginator/resource
 * collection was nested inside a plain 'data' key instead of being
 * returned directly -- which is the only way Laravel's own pagination
 * response wrapping kicks in. A couple of endpoints skipped the envelope
 * entirely and returned the raw paginator. This trait is the single place
 * that builds both the success envelope (adding meta/links whenever the
 * payload is paginated) and the error envelope, so every endpoint that
 * uses it is shaped the same way.
 */
trait ApiResponse
{
    protected function successResponse($data = null, string $message = 'Request successful', int $status = 200): JsonResponse
    {
        $paginator = $this->extractPaginator($data);

        // A Resource collection (e.g. ProductResource::collection($products))
        // already serializes to just its transformed items when nested like
        // this -- its own pagination wrapping only kicks in when Laravel
        // returns it directly as the response. A *bare* paginator (passed
        // without a Resource collection) has no such special-casing, so its
        // full data/links/meta structure would otherwise get nested inside
        // our own 'data' key; unwrap it to just this page's items instead.
        $items = ($paginator && $data instanceof LengthAwarePaginatorContract) ? $paginator->items() : $data;

        $payload = [
            'success' => true,
            'data' => $items,
            'message' => $message,
        ];

        if ($paginator) {
            $payload['meta'] = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ];

            $payload['links'] = [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ];
        }

        return response()->json($payload, $status);
    }

    protected function errorResponse(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    private function extractPaginator($data): ?LengthAwarePaginatorContract
    {
        if ($data instanceof LengthAwarePaginatorContract) {
            return $data;
        }

        if ($data instanceof AnonymousResourceCollection && $data->resource instanceof LengthAwarePaginatorContract) {
            return $data->resource;
        }

        return null;
    }
}
