<?php

namespace App\DTO;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use function response;

final class AppResponse
{
    public static function success(array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'errors' => [],
        ], $status);
    }

    public static function error(array $errors = [], int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => [],
            'errors' => $errors,
        ], $status);
    }
}
