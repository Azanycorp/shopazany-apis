<?php

namespace App\Console\Commands;

use App\Services\Email\MailingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Description('Process pending emails in batch')]
#[Signature('emails:process')]
class ProcessEmails extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MailingService $emailService): int
    {
        try {
            $emailService->sendEmails(15);
            $this->info('Emails processed successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('emails:process failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
