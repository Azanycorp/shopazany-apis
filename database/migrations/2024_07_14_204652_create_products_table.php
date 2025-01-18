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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('slug');
            $table->longText('description');
            $table->integer('category_id');
            $table->integer('sub_category_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('color_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('size_id')->nullable();
            $table->string('product_sku')->nullable();
            $table->string('product_price');
            $table->string('discount_price')->nullable();
            $table->string('price');
            $table->integer('current_stock_quantity')->default(0);
            $table->integer('minimum_order_quantity')->default(0);
            $table->string('image');
            $table->string('added_by')->nullable();
            $table->enum('status', ['pending', 'active', 'out-of-stock'])->default('pending');

            $table->index(['slug', 'category_id', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
