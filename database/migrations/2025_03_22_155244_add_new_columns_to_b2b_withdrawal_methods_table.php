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
            $table->string('type')->nullable();
            $table->string('paypal_email')->nullable();
            $table->longText('data')->nullable();
        });
    }
};
