<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BaseController extends LaravelController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Execute a callback and handle common exceptions
     */
    protected function handleRequest(callable $callback, string $errorMessage = 'An error occurred'): JsonResponse
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors(), 'Validation error');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::notFound('Resource not found');
        } catch (\Exception $e) {
            Log::error($errorMessage, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error($errorMessage . ': ' . $e->getMessage());
        }
    }

    /**
     * Log activity for auditing
     */
    protected function logActivity(string $message, $subject = null, array $properties = []): void
    {
        $log = activity();

        if (auth()->check()) {
            $log->causedBy(auth()->user());
        }

        if ($subject) {
            $log->performedOn($subject);
        }

        if (!empty($properties)) {
            $log->withProperties($properties);
        }

        $log->log($message);
    }

    /**
     * Return success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $code = 200
    ): JsonResponse {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Return error response
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        int $code = 500,
        mixed $errors = null
    ): JsonResponse {
        return ApiResponse::error($message, $code, $errors);
    }
}
