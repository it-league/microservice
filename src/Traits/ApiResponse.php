<?php

namespace itleague\microservice\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

trait ApiResponse
{
    public function respondData($data, $code = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return response()->json($data, $code, $headers);
    }

    public function respondError($msg, $code = Response::HTTP_INTERNAL_SERVER_ERROR, array $headers = []): JsonResponse
    {
        return response()->json([
            'error' => [
                'status' => $code,
                'title' => Response::$statusTexts[$code],
                'detail' => $msg,
            ]
        ], $code, $headers);
    }

    public function respondNull(int $code = Response::HTTP_NO_CONTENT, array $headers = []): JsonResponse
    {
        return response()->json(null, $code, $headers);
    }
}
