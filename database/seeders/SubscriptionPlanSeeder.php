<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionPlan::where('tier', 1)->update(['product_limit' => 20]);
        SubscriptionPlan::where('tier', 2)->update(['product_limit' => 50]);
        SubscriptionPlan::where('tier', 3)->update(['product_limit' => 75]);
    }
}
