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
        Schema::create('transaction_wallets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id')->comment('b2b seller transaction history');
            $table->string('payment_id')->nullable()->comment('Funding ref ID');
            $table->enum('type', ['deposit', 'transfer', 'debit'])->default('debit');
            $table->double('credit')->default(0);
            $table->double('debit')->default(0);
            $table->string('remark')->nullable();
            $table->text('funding_pop')->nullable();
            $table->enum('status', ['verified', 'unverified'])->nullable()->comment('handles funding');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_wallets');
    }
};
