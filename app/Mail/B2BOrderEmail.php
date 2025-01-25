<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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

    /**
     * Get the message envelope.
     */


    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->subject('Order Confirmation Mail from' . env('APP_NAME'))
            ->view(
                'mail.b2b-order-mail',
                [
                    'orderedItems' => $this->orderedItems
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
