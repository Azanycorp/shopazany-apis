<?php

namespace App\Notifications;

use App\Models\RfqMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RfqMessageNotification extends Notification
{
    use Queueable;

    public User $user;

    public RfqMessage $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, RfqMessage $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        return (new MailMessage)
            ->greeting("Hello {$this->user->full_name},")
            ->line($this->message->buyer?->full_name.' sent you a message regarding RFQ #'.$this->message->rfq->id)
            ->line('From : '.$this->message->buyer?->full_name)
            ->line('Prefered price : '.$this->message->p_unit_price)
            ->line($this->message->note)
            ->line('Kindly login to your account to reply to this message.');
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
