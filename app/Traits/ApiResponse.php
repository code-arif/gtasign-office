<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($message = null, $data = null, $code = 200)
    {
        return response()->json([
<<<<<<< HEAD
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
=======
            'status' => true ,
            'message' => $message ,
            'data' => $data ,
            'code' => $code ,

>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182
        ], $code);
    }

    public function error($data, $message = null, $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }
}
