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
        Schema::table('configurations', function (Blueprint $table) {
            //
            $table->string('withdrawal_frequency')->default('day');
            $table->string('withdrawal_status')->default('disabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            //
            $table->string('withdrawal_frequency')->default('day');
            $table->string('withdrawal_status')->default('disabled');
        });
    }
};
