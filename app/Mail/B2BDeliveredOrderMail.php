<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class B2BDeliveredOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $orderedItems;

    /**
     * Create a new message instance.
     */
    public function __construct($orderedItems, private readonly \Illuminate\Contracts\Config\Repository $repository)
    {
        $this->orderedItems = $orderedItems;
    }

    public function build()
    {
        return $this->subject('Order Delivery Confirmation Mail from '.$this->repository->get('app.name'))
            ->view(
                'mail.b2b-orderdelivery-mail',
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
