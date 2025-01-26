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
        Schema::create('configurations', function (Blueprint $table): void {
            $table->id();
            $table->double('usd_rate')->default(0)->comment('Local currency rate per USD');
            $table->integer('company_profit')->default(0)->comment('Current month profit');
            $table->enum('email_verify', ['Enabled', 'Disabled'])->nullable();
            $table->string('currency_code')->nullable()->comment('eg: USD');
            $table->string('currency_symbol')->nullable()->comment('eg: $');
            $table->date('promotion_start_date')->nullable();
            $table->date('promotion_end_date')->nullable();
            $table->enum('promo_type', ['Jolly Promo', 'Default Promo'])->nullable();
            $table->text('jolly_promo')->nullable();
            $table->double('min_deposit')->default(0);
            $table->double('max_deposit')->default(0);
            $table->double('min_withdrawal')->default(0);
            $table->double('max_withdrawal')->default(0);
            $table->double('withdrawal_fee')->default(0)->comment('In %');
            $table->double('seller_perc')->default(0)->comment('In %');
            $table->integer('paystack_perc')->default(0)->comment('Paystack charges percentage');
            $table->double('paystack_fixed')->default(0)->comment('Paystack fixed prize for charges');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
