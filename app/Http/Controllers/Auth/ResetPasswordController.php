<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    use ApiResponseTrait, ResetsPasswords;

    public static function middleware()
    {
        return [
            new Middleware('guest:api')
        ];
    }

    /**
     * 
     * @OA\Post(path="/api/password/reset",
     *   tags={"Mot de passe oublié"},
     *   summary="Réinitialisation du mot de passe",
     *   description="Réinitialisation du mot de passe",
     *   operationId="resetPassword",
     *   @OA\Parameter(
     *     name="email",
     *     required=true,
     *     in="query",
     *     description="Adresse email",
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     required=true,
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *     ),
     *     description="Nouveau mot de passe",
     *   ),
     *   @OA\Parameter(
     *     name="password_confirmation",
     *     required=true,
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *     ),
     *     description="Confirmation du nouveau mot de passe",
     *   ),
     *   @OA\Parameter(
     *     name="token",
     *     required=true,
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *     ),
     *     description="Jeton obtenu",
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Réinitialisation réussie du mot de passe",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         description="Message de réussite de réinitialisation"
     *                     ),
     *                     example={
     *                         "status": "Votre mot de passe a été réinitialisé !"
     *                     }
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Jeton invalide",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         description="Message d'échec de réinitialisation"
     *                     ),
     *                     example={
     *                         "email": "Ce jeton de réinitialisation du mot de passe n'est pas valide."
     *                     }
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Confirmation du mot de passe erronée",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Données invalides",
     *                         example="The given data was invalid."
     *                     ),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                             property="password",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="string",
     *                                 example="Le champ de confirmation mot de passe ne correspond pas."
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         }
     *   )
     * )
     * 
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return $this->successResponse([], trans($response));
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return $this->errorResponse(trans($response));
    }
}
