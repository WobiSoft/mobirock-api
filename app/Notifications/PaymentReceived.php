<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class PaymentReceived extends Notification
{
    use Queueable;

    protected $payment;
    protected $receiptType;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($payment)
    {
        $this->payment = $payment;

        $this->receiptType = (object) [
            'mime' => 'image/webp',
            'extension' => '.webp',
        ];

        if (strpos($this->payment->receipt, '.pdf'))
        {
            $this->receiptType = (object) [
                'mime' => 'application/pdf',
                'extension' => '.pdf',
            ];
        }

        if (strpos($this->payment->receipt, '.jpg'))
        {
            $this->receiptType = (object) [
                'mime' => 'image/jpeg',
                'extension' => '.jpg',
            ];
        }

        if (strpos($this->payment->receipt, '.png'))
        {
            $this->receiptType = (object) [
                'mime' => 'image/png',
                'extension' => '.png',
            ];
        }
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
            ->subject('🟡 ¡Compra Recibida!')
            ->greeting('¡Qué tal ' . $this->payment->user->first_name . '!')
            ->line('Hemos recibido tu reporte del pago con los siguientes datos:')
            ->line(new HtmlString('<ul>'))
            ->line(new HtmlString('<li><strong>Monto de la compra:</strong> $' . number_format($this->payment->amount, 2, '.', ',') . '</li>'))
            ->line(new HtmlString('<li><strong>Forma de pago:</strong> ' . $this->payment->method->name . '</li>'))
            ->line(new HtmlString('<li><strong>Cuenta del pago:</strong> ' . $this->payment->account->bank->name . ' > ' . $this->payment->account->digits . '</li>'))
            ->line(new HtmlString('<li><strong>Folio del pago:</strong> ' . $this->payment->identifier . '</li>'))
            ->line(new HtmlString('<li><strong>Fecha del pago:</strong> ' . $this->payment->date->locale('es')->isoFormat('LL') . '</li>'))
            ->line(new HtmlString('</ul>'))
            ->line('Por el momento no queda nada más que hacer, debes esperar a que se verifique y envíe tu Saldo, o en su defecto, información en caso de que se elimine.')
            ->attach($this->payment->receipt, [
                'as' => 'Comprobante_del_Pago_' . $this->payment->id . $this->receiptType->extension,
                'mime' => $this->receiptType->mime,
            ]);
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
