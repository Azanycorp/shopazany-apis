<?php

namespace App\Actions;

class SendEmailAction
{
    protected $email;

    protected $action;

    public function __construct($email, $action)
    {
        $this->email = $email;
        $this->action = $action;
    }

    public function run(): void
    {
        defer(fn () => send_email($this->email, $this->action));
    }
}
