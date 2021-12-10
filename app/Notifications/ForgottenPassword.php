<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ForgottenPassword extends Notification
{
    use Queueable;

    protected $user;
    protected $otp;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ ¿Olvidaste tu contraseña?')
            ->greeting('¡Qué tal ' . $this->user->first_name . '!')
            ->line('Hemos recibido una solicitud para renovar tu contraseña, por lo que debes introducir este código en donde se te solicitó para continuar con el proceso.')
            ->line(new HtmlString('<p style="font-size: 36px; text-align: center;"><strong>' . $this->otp . '</strong></p>'))
            ->line('Si no has solicitado un cambio de contraseña, puedes ignorar este correo.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
