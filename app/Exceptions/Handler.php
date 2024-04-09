<?php

namespace App\Exceptions;

use App\DTO\AppResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return AppResponse::error($this->convertExceptionToArray($e), $this->isHttpException($e) ? $e->getStatusCode() : 500);
    }

    protected function unauthenticated($request, AuthenticationException $exception): \Illuminate\Http\Response|JsonResponse|RedirectResponse
    {
        return $this->shouldReturnJson($request, $exception)
            ? AppResponse::error(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED)
            : redirect()->guest($exception->redirectTo($request) ?? route('login'));
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return AppResponse::error([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }
}
