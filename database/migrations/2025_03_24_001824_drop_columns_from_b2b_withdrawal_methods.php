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
        Schema::table('b2b_withdrawal_methods', function (Blueprint $table): void {
            $table->dropColumn('country_id');
            $table->dropColumn('account_type');
        });
    }
};
