<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class B2BOrderEmail extends Mailable
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
        return $this->subject('Order Confirmation Mail from'.config('app.name'))
            ->view(
                'mail.b2b-order-mail',
                [
                    'orderedItems' => $this->orderedItems,
                ]
            );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
