<?php

namespace App\Console\Commands;

use App\Enum\ProductStatus;
use App\Models\Product;
use Illuminate\Console\Command;

class CheckProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:check-product-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check product stock and update status if necessary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Product::where('current_stock_quantity', 0)
        ->where('status', ProductStatus::ACTIVE)
        ->get()
        ->each(fn($product) => $product->update(['status' => ProductStatus::OUT_OF_STOCK]));

        $this->info('Product status changed successfully.');
    }
}
