<?php

namespace App\Console\Commands;

use App\Enum\ProductStatus;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Console\Command;

class UpdateProductPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-product-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product price based on rate';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Product::with(['user'])
        ->where('status', ProductStatus::ACTIVE)
        ->chunk(100, function ($products) {
            // Get all unique currencies in one query to avoid multiple DB calls
            $currencyCodes = $products->pluck('user.default_currency')->filter()->unique()->toArray();
            $exchangeRates = $this->getExchangeRates($currencyCodes);

            $updatedProducts = [];

            foreach ($products as $product) {
                $currency = $product->user?->default_currency ?? 'USD';
                $rate = $exchangeRates[$currency] ?? null;

                if (!$rate) {
                    $this->error("Conversion rate not available for currency {$currency} for product ID {$product->id}");
                    continue;
                }

                $usdPrice = round($product->price / $rate, 2);
                $newPrice = round($usdPrice * $rate, 2);

                // Prepare data for batch update
                $updatedProducts[] = [
                    'id' => $product->id,
                    'usd_price' => $usdPrice,
                    'price' => $newPrice,
                ];
            }

            // Batch update products in one query
            if (!empty($updatedProducts)) {
                $this->batchUpdateProducts($updatedProducts);
                $this->info("Updated " . count($updatedProducts) . " products successfully.");
            }
        });
    }

    /**
     * Fetch exchange rates for multiple currencies in one query
     */
    private function getExchangeRates(array $currencyCodes): array
    {
        return Currency::whereIn('code', $currencyCodes)
            ->pluck('exchange_rate', 'code')
            ->toArray();
    }

    /**
     * Batch update products efficiently
     */
    private function batchUpdateProducts(array $updatedProducts): void
    {
        foreach ($updatedProducts as $productData) {
            Product::where('id', $productData['id'])
                ->update([
                    'usd_price' => $productData['usd_price'],
                    'price' => $productData['price'],
                ]);
        }
    }
}
