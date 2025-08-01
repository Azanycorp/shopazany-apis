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
        Schema::table('shippment_batches', function (Blueprint $table) {
            $table->string('origin_hub')->nullable();
            $table->string('destination_hub')->nullable();
            $table->string('weight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shippment_batches', function (Blueprint $table) {
            $table->dropColumn(['weight', 'origin_hub', 'destination_hub']);
        });
    }
};
