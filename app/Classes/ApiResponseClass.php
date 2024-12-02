<?php

namespace App\Classes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiResponseClass
{
    public static function rollback($e, $message = "An error occurred during the operation!")
    {
        DB::rollback();
        self::throw($e, $message);
    }

    public static function throw($e, $message = "An unexpected error occurred!")
    {
        Log::error("Error: " . $e->getMessage(), [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        $responseMessage = $message;

        if (env('APP_ENV') !== 'production') {
            $responseMessage .= ' | Error details: ' . $e->getMessage();
        }

        throw new HttpResponseException(response()->json([
            "message" => $responseMessage,
            "error_code" => 500,
        ], 500));
    }

    public static function sendResponse($result, $message = null, $code = 200)
    {
        $response = [
            "success" => true,
            'data' => $result,
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return response()->json($response, $code);
    }

    public static function sendError($message, $code = 404)
    {
        $response = [
            "success" => false,
            "message" => $message,
        ];

        return response()->json($response, $code);
    }
}