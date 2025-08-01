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
        Schema::create('buyer_shipping_addresses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('address_name');
            $table->string('name');
            $table->string('surname');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('street')->nullable();
            $table->text('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->integer('state_id');
            $table->integer('country_id');
            $table->integer('is_default')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_shipping_addresses');
    }
};
