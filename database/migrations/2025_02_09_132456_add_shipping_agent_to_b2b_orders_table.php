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
        Schema::table('b2b_orders', function (Blueprint $table): void {
            //
            $table->text('shipping_agent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_orders', function (Blueprint $table): void {
            //
            $table->text('shipping_agent')->nullable();
        });
    }
};
