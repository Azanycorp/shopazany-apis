<?php

namespace App\Console\Commands;

use App\Enum\ProductStatus;
use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Check product stock and update status if necessary')]
#[Signature('product:check-product-stock')]
class CheckProductStock extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Product::where('current_stock_quantity', 0)
            ->where('status', ProductStatus::ACTIVE)
            ->get()
            ->each(fn ($product) => $product->update(['status' => ProductStatus::OUT_OF_STOCK]));

        $this->info('Product status changed successfully.');
    }
}
