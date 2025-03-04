<?php

use Illuminate\Support\Facades\Route;
use App\Services\Email\MailingService;
use App\Console\Commands\ProcessEmails;

Route::get('/', function () {
   $email=new MailingService();
   return $email->sendEmails();
    return view('welcome');
});
