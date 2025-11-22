<?php

namespace App\Console\Commands;

use App\Enum\ProductStatus;
use App\Models\Product;
use Illuminate\Console\Command;

class FixNegativeStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fix-negative-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all products with negative stock quantity to zero.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = Product::withoutGlobalScope('in_stock')
            ->where('current_stock_quantity', '<', 0)
            ->update([
                'current_stock_quantity' => 0,
                'status' => ProductStatus::OUT_OF_STOCK,
            ]);

        $this->info("$updated products updated successfully.");
    }
}
