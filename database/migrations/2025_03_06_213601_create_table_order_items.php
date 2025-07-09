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
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('product_quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (config('database.default') !== 'sqlite') {
                $table->dropColumn('seller_id');
                $table->dropColumn('product_id');
                $table->dropColumn('product_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('product_quantity');
        });
    }
};
