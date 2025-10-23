<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

trait ApiResponseTrait
{
    protected function successResponse($data, $message = null, $code = Response::HTTP_CREATED)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = null, $code = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }

    protected function unauthorizedResponse($message = null) {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED);
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }
}
