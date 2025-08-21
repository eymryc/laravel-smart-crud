<?php

namespace Rouangni\SmartCrud\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponseTrait
{
    /**
     * Return a success response
     */
    protected function successResponse(
        $data = null, 
        string $message = 'Success', 
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
                // For Laravel Resources, merge the data directly
                $resourceData = $data->response()->getData(true);
                $response['data'] = $resourceData['data'] ?? $resourceData;
                
                // Include pagination meta if available
                if (isset($resourceData['meta'])) {
                    $response['meta'] = array_merge($resourceData['meta'], $meta);
                }
                if (isset($resourceData['links'])) {
                    $response['links'] = $resourceData['links'];
                }
            } else {
                $response['data'] = $data;
            }
        }

        if (!empty($meta)) {
            $response['meta'] = array_merge($response['meta'] ?? [], $meta);
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(
        string $message = 'Error occurred', 
        int $statusCode = 400,
        array $errors = [],
        $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(
        array $errors, 
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return a not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return an unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return a forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return a server error response
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    /**
     * Return a created response
     */
    protected function createdResponse(
        $data = null, 
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return a no content response
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return a paginated response
     */
    protected function paginatedResponse(
        $data, 
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        if (!$data instanceof ResourceCollection) {
            throw new \InvalidArgumentException('Data must be a ResourceCollection for paginated responses');
        }

        return $this->successResponse($data, $message);
    }
}