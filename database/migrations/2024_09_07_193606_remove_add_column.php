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
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('product_id');
            $table->dropColumn('order_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->bigInteger('payment_id')->after('product_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->bigInteger('product_id');
            $table->bigInteger('order_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('payment_id')->after('product_id');
        });
    }
};
