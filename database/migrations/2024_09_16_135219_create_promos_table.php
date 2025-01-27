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
        Schema::create('promos', function (Blueprint $table): void {
            $table->id();
            $table->string('coupon_code')->unique();
            $table->integer('discount');
            $table->string('discount_type');
            $table->enum('type', ['product', 'total_orders', 'welcome_coupon']);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('promo_total_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->onDelete('cascade');
            $table->decimal('minimum_cart_amount', 8, 2);
            $table->decimal('maximum_discount_amount', 8, 2);
            $table->timestamps();
        });

        Schema::create('promo_welcome_coupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->onDelete('cascade');
            $table->decimal('minimum_shopping_amount', 8, 2);
            $table->integer('number_of_days_valid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
