<?php

namespace App\Actions;

use App\Enum\PaymentType;
use App\Models\Payment;
use App\Models\PaymentLog;

class PaymentLogAction
{
    protected $data;
    protected $paymentData;
    protected $method;
    protected $status;

    public function __construct($data, $paymentData, $method, $status)
    {
        $this->data = $data;
        $this->paymentData = $paymentData;
        $this->method = $method;
        $this->status = $status;
    }

    public function execute()
    {
        try {
            $payment = Payment::create([
                'user_id' => $this->data->user_id,
                'first_name' => $this->data->first_name,
                'last_name' => $this->data->last_name,
                'email' => $this->data->email,
                'phone_number' => $this->data->phone,
                'amount' => $this->data->amount,
                'reference' => $this->data->reference,
                'channel' => $this->data->channel,
                'currency' => $this->data->currency,
                'ip_address' => $this->data->ip_address,
                'paid_at' => $this->data->paid_at,
                'created_at' => $this->data->createdAt,
                'transaction_date' => $this->data->transaction_date,
                'status' => $this->data->status,
                'type' => $this->data->type,
            ]);

            PaymentLog::create([
                'payment_id' => $payment->id,
                'data' => $this->paymentData,
                'method' => $this->method,
                'status' => $this->status,
                'type' => $this->data->type,
            ]);

            return $payment;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}











