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
        Schema::table('b2b_withdrawal_methods', function (Blueprint $table) {
            $table->dropColumn('country_id');
            $table->dropColumn('routing_number');
            $table->dropColumn('bic_swift_code');
            $table->dropColumn('routing_number');
            $table->dropColumn('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_withdrawal_methods', function (Blueprint $table) {
            //
        });
    }
};
