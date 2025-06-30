<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class UserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $baseUrl;

    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        $urls = $this->getUrls();
        $this->baseUrl = $urls['baseUrl'];
        $this->loginUrl = $urls['loginUrl'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Azany',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.user-welcome-mail',
            with: [
                'user' => $this->user,
                'baseUrl' => $this->baseUrl,
                'loginUrl' => $this->loginUrl,
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
        if (App::environment('production')) {
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
