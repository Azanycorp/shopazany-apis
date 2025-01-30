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
        Schema::create('b2b_companies', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('for b2b entities only');
            $table->string('business_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('business_reg_number')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('company_size')->nullable();
            $table->string('website')->nullable();
            $table->string('service_type')->nullable();
            $table->string('average_spend')->nullable();
            $table->string('country_id')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_companies');
    }
};
