<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class B2BUserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $baseUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        $urls = $this->getUrls();
        $this->baseUrl = $urls['baseUrl'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Azany B2B Let’s Get Your Business Thriving!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.b2b-user-welcome-mail',
            with: [
                'user' => $this->user,
                'baseUrl' => $this->baseUrl,
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

    protected function getUrls(): array
    {
        if (app()->environment('production')) {
            return [
                'baseUrl' => 'https://b2b.shopazany.com/en',
            ];
        }

        return [
            'baseUrl' => 'https://b2b.staging.shopazany.com/en',
        ];
    }
}
