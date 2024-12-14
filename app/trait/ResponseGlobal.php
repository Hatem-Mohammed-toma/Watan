<?php

namespace App\trait;


trait  ResponseGlobal
{

    public function success($data = null, $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ],$status);
    }


    public function error($message = 'Operation failed', $status = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ],$status);
}
}
