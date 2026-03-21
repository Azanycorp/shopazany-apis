<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class B2BCancelledOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected $orderedItems
    ) {}

    public function build()
    {
        return $this->subject('Order Cancellation Notification Mail from '.config('app.name'))
            ->view(
                'mail.b2b-ordercancellation-mail',
                [
                    'orderedItems' => $this->orderedItems,
                ]
            );
    }
}
