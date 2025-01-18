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
        Schema::table('b2b_products', function (Blueprint $table) {
            $table->double('unit_price')->default(0);
            $table->double('availability_quantity')->default(0);
            $table->double('quantity')->default(0);
            $table->double('sold')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_products', function (Blueprint $table) {
            //
        });
    }
};
