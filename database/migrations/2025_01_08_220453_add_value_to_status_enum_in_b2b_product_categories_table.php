<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('b2b_product_categories', function (Blueprint $table) {
            DB::statement("ALTER TABLE b2b_product_categories CHANGE COLUMN status status ENUM('active', 'inactive','in-active') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_product_categories', function (Blueprint $table) {
            //
        });
    }
};
