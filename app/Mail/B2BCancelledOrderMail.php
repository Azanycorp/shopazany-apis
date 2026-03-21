<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class B2BCancelledOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $orderedItems;

    /**
     * Create a new message instance.
     */
    public function __construct($orderedItems)
    {
        $this->orderedItems = $orderedItems;
    }

    public function build()
    {
        return $this->subject('Order Cancellation Confirmation Mail from'.config('app.name'))
            ->view(
                'mail.b2b-ordercancellation-mail',
                [
                    'orderedItems' => $this->orderedItems,
                ]
            );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
