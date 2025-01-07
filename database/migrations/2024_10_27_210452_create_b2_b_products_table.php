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
        Schema::create('b2b_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('slug');
            $table->bigInteger('category_id');
            $table->bigInteger('sub_category_id')->nullable();
            $table->json('keywords');
            $table->longText('description');
            $table->string('front_image');
            $table->double('minimum_order_quantity')->default(0);
            $table->double('unit_price')->default(0);
            $table->double('fob_price')->default(0);
            $table->integer('country_id');
            $table->string('status')->default('pending');

            $table->index(['name', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('b2b_product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('b2b_product_id');
            $table->string('image');

            $table->foreign('b2b_product_id')->references('id')->on('b2b_products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2_b_products');
    }
};
