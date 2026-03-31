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
        Schema::table('rfqs', function (Blueprint $table) {
            $table->decimal('seller_unit_price', 19, 4)->default(0.0000);
            $table->decimal('buyer_unit_price', 19, 4)->default(0.0000);
            $table->decimal('buyer_total_amount', 19, 4)->default(0.0000);
            $table->decimal('seller_total_amount', 19, 4)->default(0.0000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn(['seller_unit_price', 'buyer_unit_price', 'buyer_total_amount', 'seller_total_amount']);
        });
    }
};
