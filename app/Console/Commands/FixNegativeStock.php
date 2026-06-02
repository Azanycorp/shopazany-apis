<?php

namespace App\Console\Commands;

use App\Enum\ProductStatus;
use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Updates all products with negative stock quantity to zero.')]
#[Signature('products:fix-negative-stock')]
class FixNegativeStock extends Command
{
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
