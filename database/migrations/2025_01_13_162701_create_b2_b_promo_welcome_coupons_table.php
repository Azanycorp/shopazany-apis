<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('b2b_promo_welcome_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('b2b_promo_id')->constrained('b2b_promos')->onDelete('cascade');
            $table->decimal('minimum_shopping_amount', 8, 2);
            $table->integer('number_of_days_valid');
            $table->timestamps();
        });
        Schema::create('b2b_promo_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('b2b_promo_id')->constrained('b2b_promos')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('b2b_products')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('b2b_promo_total_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('b2b_promo_id')->constrained('b2b_promos')->onDelete('cascade');
            $table->decimal('minimum_cart_amount', 8, 2);
            $table->decimal('maximum_discount_amount', 8, 2);
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2_b_promo_welcome_coupons');
        Schema::dropIfExists('b2b_promo_products');
        Schema::dropIfExists('b2b_promo_total_orders');
    }
};
