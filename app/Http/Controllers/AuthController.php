<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public static function middleware()
    {
        return [
            new Middleware('auth:api', null, ['register', 'login'])
        ];
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return $this->errorResponse('Could not create token', 500);
        }

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new UserResource($user)
        ], 'Inscription effectuée avec succès');
    }

    /**
     * Handle a login request to the application.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Post(
     *   path="/api/login",
     *   tags={"Authentification"},
     *   summary="Connecte l'utilisateur au système",
     *   description="Connexion de l'utilisateur au système",
     *   operationId="loginUser",
     *   @OA\Parameter(
     *     name="email",
     *     required=true,
     *     in="query",
     *     description="L'email de l'utilisateur pour la connexion",
     *     @OA\Schema(
     *          type="string",
     *          example="john@gmail.com"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     required=true,
     *     in="query",
     *     @OA\Schema(
     *          type="string",
     *          example="12345"
     *     ),
     *     description="Le mot de passe de connexion",
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Authentification réussie",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="token",
     *                         type="string",
     *                         description="Un jeton"
     *                     ),
     *                     @OA\Property(
     *                         property="token_type",
     *                         type="string",
     *                         description="Le type de token"
     *                     ),
     *                     @OA\Property(
     *                         property="expires_in",
     *                         type="integer",
     *                         description="La durée d'expiration du token"
     *                     ),
     *                     example={
     *                         "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6MjAyM1wvYXBpXC9sb2dpbiIsImlhdCI6MTYxOTExNTU1OCwiZXhwIjoxNjE5MjAxOTU4LCJuYmYiOjE2MTkxMTU1NTgsImp0aSI6Ilp4N0VTYjNwZGVOQW9oUFgiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.X7wwPIsEzEVFkB1R69y5-harbqjqki5Yon6DyKZCOOc",
     *                         "token_type": "bearer",
     *                         "expires_in": 86400
     *                     }
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Informations d'identification invalides",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Message d'échec d'authentification",
     *                         example="The given data was invalid."
     *                     ),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                             property="email",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="string",
     *                                 example="Ces identifiants ne correspondent pas à nos enregistrements."
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Adresse de courriel non vérifiée",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Message d'échec d'authentification",
     *                         example="The given data was invalid."
     *                     ),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                             property="email",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="string",
     *                                 example="Vous devez d'abord vérifier votre adresse e-mail."
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         }
     *   )
     * )
     *
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->errorResponse('Email ou mot de passe incorrect');
            }
        } catch (JWTException $e) {
            return $this->errorResponse('Could not create token', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new UserResource(Auth::user())
        ], 'Authentification réussie');
    }

    /**
     * Get the authenticated User.
     * @return UserResource
     */
    public function me()
    {
        return new UserResource(Auth::user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            return $this->errorResponse('Failed to logout, please try again', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse([], 'Successfully logged out');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

}
