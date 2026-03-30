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
            $table->double('seller_unit_price')->default(0);
            $table->double('buyer_unit_price')->default(0);
            $table->double('buyer_total_amount')->default(0);
            $table->double('seller_total_amount')->default(0);
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
