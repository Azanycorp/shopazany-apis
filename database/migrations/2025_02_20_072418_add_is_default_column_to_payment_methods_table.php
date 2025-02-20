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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('data');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->integer('total_used')->default(0)->after('used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('total_used');
        });
    }
};
