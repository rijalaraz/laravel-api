<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    use ApiResponseTrait, RegistersUsers;

    public static function middleware()
    {
        return [
            new Middleware('guest:api')
        ];
    }

    /**
     * Store a newly created user in storage.
     * @OA\Post(
     *     path="/api/register",
     *     summary="Enregistrer un nouvel utilisateur",
     *     description="Enregistrer un nouvel utilisateur",
     *     operationId="registerUser",
     *     tags={"Inscription"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Le nom de l'utilisateur",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *              example="John Doe"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="L'email de l'utilisateur",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *              example="john@gmail.com"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Mot de passe",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *              format="password",
     *              example="Mija6!"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         description="Confirmation du mot de passe",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *              format="password",
     *              example="Mija6!"
     *         )
     *     ),
     *     @OA\Response(response="201", description="Utilisateur enregistré avec succes"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function __invoke(Request $request)
    {
        $validator = $this->validator($request->all());

        if($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $uniqueEmailRule = Rule::unique('users', 'email');
        return Validator::make($data, [
            'email' => ['required', 'email:rfc,dns', 'max:255', $uniqueEmailRule],
            'password' => 'required|min:6|pwned|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/', // at-least 1 Uppercase, 1 Lowercase, 1 Numeric and 1 special character
            'password_confirmation' => 'required|min:6',
            'name' => 'required|max:255',
        ]);
    }

    /**
     * Create user.
     *
     * @param  array  $data
     * @return User   $user
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'remember_token' => Hash::make(Str::random(10)),
        ]);

        return $user;
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
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
        ], 'Inscription effectuée avec succès. '.trans('verification.sent'));
    }
}
