<?php

/*
|--------------------------------------------------------------------------
| Password Reset Language Lines
|--------------------------------------------------------------------------
|
| The following language lines are the default lines which match reasons
| that are given by the password broker for a password update attempt
| has failed, such as for an invalid token or invalid new password.
|
*/

return [
    'reset'     => 'Votre mot de passe a été réinitialisé !',
    'sent'      => 'Un courriel de réinitialisation vous a été envoyé à :email. <i>Il se pourrait qu\'il soit dans votre dossier de courriels indésirables</i>.',
    'throttled' => 'Veuillez patienter avant de réessayer.',
    'token'     => 'Ce jeton de réinitialisation du mot de passe n\'est pas valide.',
    'user'      => 'Aucun utilisateur n\'a été trouvé avec cette adresse email.',
    'message'   => [
        'greeting'  => 'Bonjour!',
        'subject'   => 'Réinitialisation de votre mot de passe :app_name',
        'line1'     => 'Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.',
        'action'    =>  [
            'text'  => 'Réinitialiser le mot de passe',
            'url'   => ':app_url/auth/password-reset/:token',
        ],
        'line2'     => 'Ce lien de réinitialisation de mot de passe expirera dans :count minutes.',
        'line3'     => 'Si vous n’avez pas demandé de réinitialisation de mot de passe, aucune autre action n’est requise.',
        'salutation' => 'Salutations',
    ]
];
