<?php

namespace App\Console\Commands;

use App\Services\Email\MailingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending emails in batch';

    /**
     * Execute the console command.
     */
    public function handle(MailingService $emailService)
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
