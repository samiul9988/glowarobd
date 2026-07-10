<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ResponseHelper
{
    /**
     * Generate a success response.
     *
     * @param mixed $data The data to include in the response.
     * @param string $message A success message.
     * @param int $statusCode The HTTP status code (default: 200).
     * @return JsonResponse
     */
    public static function success(string $message = 'Success', int $statusCode = 200, $data = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        if($data !== null) {
            $response['data'] = $data;
        }
        return response()->json($response, $statusCode);
    }

    public static function paginated($data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'links' => [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $data->currentPage(),
                'from' => $data->firstItem(),
                'last_page' => $data->lastPage(),
                'links' => $data->linkCollection(),
                'path' => $data->path(),
                'per_page' => $data->perPage(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
            ],
        ], $statusCode);
    }

    public static function successtoken(string $message = 'Success', int $statusCode = 200, $token = null): JsonResponse
    {
        $response = [
            'success' => true,
            'token' => $token,
            'message' => $message,
        ];
        return response()->json($response, $statusCode);
    }

    /**
     * Generate an error response.
     *
     * @param string $message An error message.
     * @param int $statusCode The HTTP status code (default: 400).
     * @param mixed $errors Additional error details (optional).
     * @return JsonResponse
     */
    public static function error(string $message = 'Server Error', int $statusCode = 500, $errors = null): JsonResponse
    {
        $data = [
            'success' => false,
            'message' => $message,
        ];
        if($errors !== null) {
            $data['errors'] = $errors;
        }
        return response()->json($data, $statusCode);
    }


    /**
     * Generate a not found response.
     *
     * @param string $message A not found message.
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource Not Found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Generate an unauthorized response.
     *
     * @param string $message An unauthorized message.
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Generate a forbidden response.
     *
     * @param string $message A forbidden message.
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Generate a server error response.
     *
     * @param string $message A server error message.
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Internal Server Error'): JsonResponse
    {
        return self::error($message, 500);
    }
}
