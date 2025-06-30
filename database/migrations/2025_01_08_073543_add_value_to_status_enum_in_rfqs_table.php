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
        Schema::table('rfqs', function (Blueprint $table): void {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE rfqs CHANGE COLUMN status status ENUM('pending', 'review', 'in-progress', 'shipped', 'confirmed', 'cancelled', 'delivered') NOT NULL");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table): void {
            //
        });
    }
};
