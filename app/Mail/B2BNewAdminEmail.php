<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class B2BNewAdminEmail extends Mailable
{
    use Queueable, SerializesModels;
    protected $loginDetails;
    /**
     * Create a new message instance.
     */
    public function __construct($loginDetails)
    {
        $this->loginDetails = $loginDetails;
    }

    /**
     * Get the message envelope.
     */


    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->subject('Account Creation Login Details')
            ->view(
                'mail.b2b-new-admin',
                [
                    'loginDetails' => $this->loginDetails
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
