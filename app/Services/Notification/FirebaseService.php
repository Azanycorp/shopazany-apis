<?php

namespace App\Services\Notification;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    public function __construct(private Messaging $messaging) {}

    public function sendToToken(string $deviceToken, string $title, string $body, array $data = [])
    {
        $message = CloudMessage::fromArray([
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);

        return $this->messaging->send($message);
    }
}
