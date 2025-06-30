<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Services\Curl\GetCurl;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Bank::count() === 0) {
            $url = config('services.paystack.bank_base_url');
            $token = config('services.paystack.test_sk');

            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ];

            $banks = (new GetCurl($url, $headers))->execute();

            foreach ($banks as $bank) {
                Bank::create([
                    'name' => $bank['name'],
                    'slug' => $bank['slug'],
                    'code' => $bank['code'],
                    'currency' => $bank['currency'],
                ]);
            }
        } else {
            $this->command->info('Bank table is not empty. Skipping seeding.');
        }
    }
}
