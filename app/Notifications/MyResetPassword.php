<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MyResetPassword extends ResetPassword
{
    use Queueable;

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return $this->buildMailMessage($this->resetUrl($notifiable));
    }

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(trans('passwords.message.subject', [
                'app_name' => config('app.name')
            ]))
            ->markdown('mail.my-reset-password', [
                'greeting' => trans('passwords.message.greeting'),
                'line1' => trans('passwords.message.line1'),
                'buttonText' => trans('passwords.message.action.text'),
                'buttonUrl' => trans('passwords.message.action.url', [
                    'app_url'   =>  url(config('app.url')),
                    'token'     =>  $this->token,
                    // 'email'     =>  urlencode($notifiable->email),
                ]),
                'line2' => trans('passwords.message.line2', [
                    'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')
                ]),
                'line3' => trans('passwords.message.line3'),
                'salutation' => trans('passwords.message.salutation'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
