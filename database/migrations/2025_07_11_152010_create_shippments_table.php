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
        Schema::create('shippments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hub_id');
            $table->string('shippment_id')->unique();
            $table->json('package')->nullable();
            $table->json('customer')->nullable();
            $table->json('vendor')->nullable();
            $table->string('status')->default('in-transit');
            $table->string('priority')->default('low');
            $table->string('expected_delivery_date')->nullable();
            $table->string('start_origin')->nullable();
            $table->string('current_location')->nullable();
            $table->text('activity')->nullable();
            $table->text('note')->nullable();
            $table->double('items')->default(0);
            $table->string('dispatch_name')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('dispatch_phone')->nullable();
            $table->string('expected_delivery_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippments');
    }
};
