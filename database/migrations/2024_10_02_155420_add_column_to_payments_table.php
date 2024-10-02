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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('type')->after('status')->nullable();
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->string('type')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};