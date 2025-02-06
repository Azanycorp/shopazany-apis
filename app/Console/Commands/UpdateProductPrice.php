<?php

namespace App\Console\Commands;

use App\Enum\ProductStatus;
use App\Models\Currency;
use App\Models\Product;
use App\Services\Curl\CurrencyConversionService;
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
        $products = Product::with(['user', 'admin'])
            ->where('status', ProductStatus::ACTIVE)
            ->get();

        foreach ($products as $product) {
            $currency = $product?->user?->default_currency;

            $rate = $this->fetchConversionRate($currency);

            if ($rate) {
                $price = $product->price;

                $usdPrice = round($price / $rate, 2);

                $newPrice = round($usdPrice * $rate, 2);

                $product->usd_price = $usdPrice;
                $product->price = $newPrice;

                $product->save();

                $this->info("Updated product ID {$product->id} with new price: {$usdPrice} {$currency}");
            } else {
                $this->error("Conversion rate not available for currency {$currency} for product ID {$product->id}");
            }
        }
    }

    /**
     * Fetch conversion rate against USD for a given currency.
     * Replace this with actual implementation for fetching rates.
     */
    private function fetchConversionRate(string $currency): ?float
    {
        $currencyRecord = Currency::where('code', $currency)->first();

        if (! $currencyRecord) {
            echo "Conversion rate not available for currency {$currency}.";
            return null;
        }

        return (float) $currencyRecord->exchange_rate;
    }
}
