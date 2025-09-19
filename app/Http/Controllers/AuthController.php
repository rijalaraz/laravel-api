<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', '=', $request->email)->with('projects')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Email ou mot de passe incorrect');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user)
        ], 'Authentification réussie');
    }
}
