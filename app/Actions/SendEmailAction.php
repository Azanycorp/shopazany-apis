<?php

namespace App\Actions;

use Illuminate\Support\Facades\Mail;

class SendEmailAction
{
    protected $email;
    protected $action;

    public function __construct($email, $action)
    {
        $this->email = $email;
        $this->action = $action;
    }

    public function run()
    {
        Mail::to($this->email)->send($this->action);
    }
}


