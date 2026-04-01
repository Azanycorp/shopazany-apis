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
        Schema::table('b2b_orders', function (Blueprint $table) {
            $table->decimal('seller_unit_price', 10, 2)->default(0.00);
            $table->decimal('buyer_unit_price', 10, 2)->default(0.00);
            $table->decimal('buyer_total_amount', 10, 2)->default(0.00);
            $table->decimal('seller_total_amount', 10, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_orders', function (Blueprint $table) {
            $table->dropColumn(['seller_unit_price', 'buyer_unit_price', 'buyer_total_amount', 'seller_total_amount']);

        });
    }
};
