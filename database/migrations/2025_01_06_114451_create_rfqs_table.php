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
        Schema::create('rfqs', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('buyer_id');
            $table->bigInteger('seller_id');
            $table->string('quote_no')->nullable();
            $table->integer('product_id');
            $table->double('product_quantity')->default(0)->comment('the MOQ of the product');
            $table->double('p_unit_price')->default(0)->comment('preferred_unit_price');
            $table->double('total_amount')->default(0);
            $table->longText('product_data')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('status')->default('pending');
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
        Schema::dropIfExists('rfqs');
    }
};
