<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('active', 'pending', 'out-of-stock', 'deleted') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('active', 'pending', 'out-of-stock') NOT NULL");
        }
    }
};
