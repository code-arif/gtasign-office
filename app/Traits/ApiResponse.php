<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Unified API Response
     */
    protected function respond(
        bool $success,
        ?string $message = null,
        mixed $data = null,
        mixed $errors = null,
        int $code = 200
    ) {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
            'code'    => $code,
        ], $code);
    }

    /**
     * Success Response
     */
    protected function success(
        ?string $message = null,
        mixed $data = null,
        int $code = 200
    ) {
        return $this->respond(true, $message, $data, null, $code);
    }

    /**
     * Error Response
     * @param mixed $errors - Error details (array or string)
     * @param string|null $message - Error message
     * @param int $code - HTTP status code
     */
    protected function error(
        mixed $errors = null,
        ?string $message = null,
        int $code = 500
    ) {
        return $this->respond(false, $message, null, $errors, $code);
    }

    /**
     * Validation Error Response
     */
    protected function validationError(
        mixed $errors,
        ?string $message = 'Validation failed',
        int $code = 422
    ) {
        return $this->respond(false, $message, null, $errors, $code);
    }


    /**
     * Not found response
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error(null, $message, 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error(null, $message, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error(null, $message, 403);
    }
}
