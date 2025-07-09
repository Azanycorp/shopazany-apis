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
            //
            $table->integer('is_default')->after('bic_swift_code')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_withdrawal_methods', function (Blueprint $table): void {
            //
            $table->integer('is_default')->after('bic_swift_code')->default(0);
        });
    }
};
