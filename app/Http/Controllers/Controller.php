<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Success response method.
     *
     * @param mixed $data
     * @param string|null $dataKey
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data = null, ?string $dataKey = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            if ($dataKey) {
                $response[$dataKey] = $data;
            } else {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     *
     * @param string $message
     * @param \Exception|null $exception
     * @param int $code
     * @return JsonResponse
     */
    protected function errorResponse(string $message, ?\Exception $exception = null, int $code = 500): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($exception && config('app.debug')) {
            $response['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        return response()->json($response, $code);
    }

    /**
     * Not found response method.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 404);
    }
}
