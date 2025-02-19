<?php

namespace App\Services;

use App\Enum\TransactionStatus;
use App\Services\Curl\PostCurl;

class PayoutService
{
    public static function transfer($user, $fields)
    {
        $url = "https://api.paystack.co/transfer";
        $token = config('paystack.secretKey');

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $data = (new PostCurl($url, $headers, $fields))->execute();

        if($data['status'] === false) {
            return response()->json([
                'status' => $data['status'],
                'message' => $data['message'],
                'data' => null
            ], 400);
        }

        (new TransactionService(
            $user,
            TransactionStatus::TRANSFER,
            $fields['amount'],
            $data['status']
        ))->logTransaction();

        return response()->json([
            'status' => $data['status'],
            'message' => $data['message'],
            'data' => null
        ], 200);
    }
}

