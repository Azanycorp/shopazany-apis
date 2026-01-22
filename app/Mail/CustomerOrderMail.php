<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected User $user,
        protected array $items,
        protected string $orderNo,
        protected float $totalAmount
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yay! Your Azany Order is on its way!',
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
                'accountLink' => $this->getUrls()['baseUrl'],
                'loginUrl' => $this->getUrls()['loginUrl'],
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

    protected function getUrls(): array
    {
        if (app()->environment('production')) {
            return [
                'baseUrl' => 'https://shopazany.com/en',
                'loginUrl' => 'https://shopazany.com/en/login',
            ];
        }

        return [
            'baseUrl' => 'https://fe-staging.shopazany.com/en',
            'loginUrl' => 'https://fe-staging.shopazany.com/en/login',
        ];
    }
}
