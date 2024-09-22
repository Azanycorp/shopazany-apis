<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $items;
    protected $orderNo;
    protected $totalAmount;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $items, $orderNo, $totalAmount)
    {
        $this->user = $user;
        $this->items = $items;
        $this->orderNo = $orderNo;
        $this->totalAmount = $totalAmount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Placed Successfully',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.customer-order-mail',
            with: [
                'user' => $this->user,
                'items' => $this->items,
                'orderNo' => $this->orderNo,
                'totalAmount' => $this->totalAmount,
            ],
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
