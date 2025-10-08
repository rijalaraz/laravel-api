<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    use ApiResponseTrait;

    public static function middleware()
    {
        return [
            new Middleware('throttle:6,1')
        ];
    }

    /**
     * @OA\Get(path="/api/email/verify/{user}",
     *   tags={"Inscription"},
     *   summary="Vérification de l'adresse email d'un utilisateur inscrit",
     *   description="Vérification de l'adresse email d'un utilisateur inscrit",
     *   operationId="verifyEmailAddress",
     *   @OA\Parameter(
     *      description="Id de l'utilisateur",
     *      in="path",
     *      name="user",
     *      required=true,
     *      @OA\Schema(
     *          type="integer",
     *          example=34
     *      )
     *   ),
     *   @OA\Parameter(
     *     name="expires",
     *     required=true,
     *     in="query",
     *     @OA\Schema(
     *          type="string",
     *          example="1619635176"
     *     ),
     *     description="Date d'expiration",
     *   ),
     *   @OA\Parameter(
     *     name="signature",
     *     required=true,
     *     in="query",
     *     description="Signature",
     *     @OA\Schema(
     *          type="string",
     *          example="a0dbd2d1bb02980b25881132262dae0ae9780d9c51a2c7fe79359badeb0dd508"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Vérification réussie",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                          property="status",
     *                          type="string",
     *                          description="Message de réussite de la vérification",
     *                          example="Votre adresse email est vérifiée."
     *                     ),
     *                     @OA\Property(
     *                          property="user",
     *                          type="object",
     *                          description="Détails de l'utilisateur",
     *                              @OA\Property(
     *                                  property="id",
     *                                  type="integer",
     *                                  description="ID",
     *                                  example=20
     *                              ),
     *                              @OA\Property(
     *                                  property="name",
     *                                  type="string",
     *                                  description="Nom d'usager",
     *                                  example=null
     *                              ),
     *                              @OA\Property(
     *                                  property="firstname",
     *                                  type="string",
     *                                  description="Prénom(s)",
     *                                  example=null
     *                              ),
     *                              @OA\Property(
     *                                  property="lastname",
     *                                  type="string",
     *                                  description="Nom",
     *                                  example=null
     *                              ),
     *                              @OA\Property(
     *                                  property="email",
     *                                  type="string",
     *                                  description="Email",
     *                                  example="mathieu.roy@strategemedia.com"
     *                              ),
     *                              @OA\Property(
     *                                  property="company",
     *                                  type="string",
     *                                  description="Compagnie",
     *                                  example="Alu-Rex"
     *                              ),
     *                              @OA\Property(
     *                                  property="mobilephone",
     *                                  type="string",
     *                                  description="Cellulaire",
     *                                  example=null
     *                              ),
     *                              @OA\Property(
     *                                  property="phone",
     *                                  type="string",
     *                                  description="Téléphone",
     *                                  example=null
     *                              ),
     *                              @OA\Property(
     *                                  property="email_verified_at",
     *                                  type="string",
     *                                  description="Date de vérification de l'email",
     *                                  example="2021-04-28T21:28:53.000000Z"
     *                              ),
     *                              @OA\Property(
     *                                  property="created_at",
     *                                  type="string",
     *                                  description="Date de création",
     *                                  example="2021-04-28T21:21:35.000000Z"
     *                              ),
     *                              @OA\Property(
     *                                  property="updated_at",
     *                                  type="string",
     *                                  description="Date de mis à jour",
     *                                  example="2021-04-28T21:28:53.000000Z"
     *                              ),
     *                              @OA\Property(
     *                                  property="deleted_at",
     *                                  type="string",
     *                                  description="Date de suppression",
     *                                  example=null
     *                              ),
     *                              @OA\Property(
     *                                  property="stripe_id",
     *                                  type="string",
     *                                  description="Identifiant Client de Stripe",
     *                                  example="cus_JTGwkBQuLY8Nqy"
     *                              ),
     *                              @OA\Property(
     *                                  property="card_brand",
     *                                  type="string",
     *                                  description="Type de carte de paiement",
     *                                  example="mastercard"
     *                              ),
     *                              @OA\Property(
     *                                  property="card_last_four",
     *                                  type="string",
     *                                  description="Les 4 derniers chiffres de la carte",
     *                                  example="4444"
     *                              ),
     *                              @OA\Property(
     *                                  property="trial_ends_at",
     *                                  type="string",
     *                                  description="Date de fin de l'essai",
     *                                  example=null
     *                              )
     *                     )
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Vérification échouée",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                          property="status",
     *                          type="string",
     *                          description="Message d'échec de vérification",
     *                          example="La vérification de votre adresse email a échouée."
     *                     )
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Adresse email déjà vérifiée",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         description="Vérification déjà faite",
     *                         example="Votre adresse email a déjà été vérifiée."
     *                     )
     *                 )
     *             )
     *         }
     *   )
     * )
     *
     * Mark the user's email address as verified.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, User $user)
    {
        if (!URL::hasValidSignature($request)) {
            return $this->errorResponse(trans('verification.invalid'));
        }

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse(trans('verification.already_verified'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->markEmailAsVerified();

        // $user->assignRole('Administrateur');

        event(new Verified($user));

        return $this->successResponse([
            'email' => $user->getEmailForVerification()
        ],trans('verification.verified'));

        // return redirect(url(config('app.url')));
    }

    /**
     * @OA\Post(path="/api/email/resend",
     *   tags={"Inscription"},
     *   summary="Renvoi du courriel de vérification de l'adresse email d'un utilisateur inscrit",
     *   description="Renvoi du courriel de vérification de l'adresse email d'un utilisateur inscrit",
     *   operationId="resendEmailVerification",
     *   @OA\Parameter(
     *     name="email",
     *     required=true,
     *     in="query",
     *     description="Adresse de courriel",
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Renvoi de courriel réussi",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         description="Message de confirmation d'envoi d'email",
     *                         example="Un courriel de vérification d'adresse email vous a été envoyé."
     *                     )
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=208,
     *     description="Adresse de courriel déjà vérifiée",
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
     *                             property="email",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="string",
     *                                 example="Votre adresse email a déjà été vérifiée."
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Adresse de courriel invalide",
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
     *                             property="email",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="string",
     *                                 example="Le champ adresse email doit être une adresse email valide."
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         }
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Utilisateur inexistant",
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
     *                             property="email",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="string",
     *                                 example="Aucun utilisateur n'a été trouvé avec cette adresse email."
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         }
     *   )
     * )
     *
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            return $this->errorResponse(trans('verification.user'));
        }

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse(trans('verification.already_verified'), Response::HTTP_ALREADY_REPORTED);
        }

        $user->sendEmailVerificationNotification();

        return $this->successResponse([], trans('verification.sent'));
    }
}
