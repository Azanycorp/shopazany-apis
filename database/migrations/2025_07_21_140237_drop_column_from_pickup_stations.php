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
        Schema::table('pickup_stations', function (Blueprint $table) {
            $table->dropColumn('collation_center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_stations', function (Blueprint $table) {
            $table->unsignedBigInteger('collation_center_id');
        });
    }
};
