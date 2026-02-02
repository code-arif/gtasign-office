<?php

namespace App\Traits;

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
     */
    protected function error(
        ?string $message = null,
        mixed $errors = null,
        int $code = 500
    ) {
        return $this->respond(false, $message, null, $errors, $code);
    }


    /*
    * Validation error
    */
    public function validationError($errors, $message = 'Validation failed', $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => [],
            'errors'  => $errors,
            'code'    => $code
        ], $code);
    }
}
