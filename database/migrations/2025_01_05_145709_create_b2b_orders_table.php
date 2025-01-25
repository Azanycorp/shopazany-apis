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
        Schema::create('b2b_orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('buyer_id');
            $table->bigInteger('seller_id');
            $table->integer('product_id');
            $table->double('product_quantity')->comment('the MOQ of the product');
            $table->string('order_no')->nullable();
            $table->longText('shipping_address')->nullable();
            $table->longText('product_data')->nullable();
            $table->double('total_amount')->default(0);
            $table->string('payment_method');
            $table->enum('payment_status',['paid','unpaid'])->default('unpaid');
            $table->enum('status', ['pending', 'shipped','in-progress','confirmed','cancelled','delivered'])->default('pending');
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('shipped_date')->nullable();
            $table->index(['buyer_id', 'seller_id', 'product_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_orders');
    }
};
