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
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('seller_id');
            $table->integer('product_id');
            $table->integer('product_quantity');
            $table->string('order_no');
            $table->longText('shipping_address');
            $table->dateTime('order_date');
            $table->string('total_amount');
            $table->string('payment_method');
            $table->string('payment_status')->nullable();
            $table->enum('status', ['pending', 'processing', 'confimed', 'shipped', 'delivered', 'cancelled'])->default('pending');

            $table->index(['user_id', 'seller_id', 'product_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
