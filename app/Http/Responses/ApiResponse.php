<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a success JSON response
     */
    public static function success(
        mixed $data = null,
        string $message = 'Operation successful',
        int $code = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return an error JSON response
     */
    public static function error(
        string $message = 'An error occurred',
        int $code = 500,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error response
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation error'
    ): JsonResponse {
        return self::error($message, 422, $errors);
    }

    /**
     * Return a not found response
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Return an unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Return a forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }
}
