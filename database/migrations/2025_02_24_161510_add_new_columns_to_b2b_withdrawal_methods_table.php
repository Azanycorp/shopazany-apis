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
            $table->string('platform')->nullable();
            $table->string('recipient')->nullable();
            $table->text('reference')->nullable();
            $table->text('recipient_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_withdrawal_methods', function (Blueprint $table) {
            $table->string('platform')->nullable();
            $table->string('recipient')->nullable();
            $table->text('reference')->nullable();
            $table->text('recipient_code')->nullable();
        });
    }
};
