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
        Schema::table('collation_centers', function (Blueprint $table) {
            $table->text('city')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collation_centers', function (Blueprint $table) {
            //
        });
    }
};
