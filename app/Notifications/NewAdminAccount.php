<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAdminAccount extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database']; // Store in DB
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'New user registered: ' . $this->user->name,
            'user_id' => $this->user->id,
        ];
    }
}
