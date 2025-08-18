<?php
// src/Traits/ApiResponseTrait.php

namespace Rouangni\SmartCrud\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse(mixed $data = null, string $message = "", int $status = 200): JsonResponse
    {
        $response = [
            config('smart-crud.api.format.success_key') => true,
            config('smart-crud.api.format.message_key') => $message ?? config('smart-crud.api.messages.retrieved'),
            config('smart-crud.api.format.status_key') => $status
        ];

        if ($data !== null) {
            $response[config('smart-crud.api.format.data_key')] = $data;
        }

        return response()->json($response, $status);
    }

    protected function errorResponse(string $message = "", int $status = 400, array $errors = []): JsonResponse
    {
        $response = [
            config('smart-crud.api.format.success_key') => false,
            config('smart-crud.api.format.message_key') => $message ?? config('smart-crud.api.messages.server_error'),
            config('smart-crud.api.format.status_key') => $status
        ];

        if (!empty($errors)) {
            $response[config('smart-crud.api.format.errors_key')] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse(
            config('smart-crud.api.messages.validation_failed'), 
            422, 
            $errors
        );
    }
}