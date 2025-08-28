<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory(20)->create()->each(function ($order) {
            $products = Product::with(['productVariations', 'user.wallet'])
                ->inRandomOrder()
                ->take(rand(1, 4))
                ->get();

            $totalAmount = 0;

            foreach ($products as $product) {
                $quantity = rand(1, 3);
                $variation = $product->productVariations->isNotEmpty() ? $product->productVariations->random() : null;

                if ($variation) {
                    $price = $variation->price;
                    $subTotal = $price * $quantity;

                    $order->products()->attach($product->id, [
                        'product_quantity' => $quantity,
                        'price' => $price,
                        'sub_total' => $subTotal,
                        'status' => $order->status,
                        'variation_id' => $variation->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $variation->decrement('stock', $quantity);
                } else {
                    $price = $product->price;
                    $subTotal = $price * $quantity;

                    $order->products()->attach($product->id, [
                        'product_quantity' => $quantity,
                        'price' => $price,
                        'sub_total' => $subTotal,
                        'status' => $order->status,
                        'variation_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $product->decrement('current_stock_quantity', $quantity);
                }

                // increment vendor wallet
                if ($product->user) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $product->user->id],
                        ['balance' => 0]
                    );

                    $wallet->increment('balance', $subTotal);
                }

                $totalAmount += $subTotal;
            }

            // Update order total after attaching products
            $order->update(['total_amount' => $totalAmount]);
        });
    }
}
