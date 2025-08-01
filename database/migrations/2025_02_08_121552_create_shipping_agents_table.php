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
        Schema::create('shipping_agents', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('logo')->nullable();
            $table->json('country_ids');
            $table->string('account_email')->nullable();
            $table->string('account_password')->nullable();
            $table->string('api_live_key')->nullable();
            $table->string('api_test_key')->nullable();
            $table->string('status')->default('test');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_agents');
    }
};
