<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('b2b_product_sub_categories', function (Blueprint $table): void {
                DB::statement("ALTER TABLE b2b_product_sub_categories CHANGE COLUMN status status ENUM('active', 'inactive','in-active') NOT NULL");

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_product_sub_categories', function (Blueprint $table): void {
            //
        });
    }
};
