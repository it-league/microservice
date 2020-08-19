<?php

namespace itleague\microservice\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponse
{
    public function respondData(?array $data, int $code = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return response()->json($data, $code, $headers);
    }

    /**
     * @param array|string $msg
     * @param int $code
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondError($msg, int $code = Response::HTTP_INTERNAL_SERVER_ERROR, array $headers = []): JsonResponse
    {
        return $this->respondData([
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
