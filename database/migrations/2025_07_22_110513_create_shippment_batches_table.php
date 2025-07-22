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
        Schema::create('shippment_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collation_id');
            $table->json('shippment_ids');
            $table->double('items')->default(0);
            $table->string('status')->default('in-transit');
            $table->string('priority')->default('low');
            $table->string('destination_state')->nullable();
            $table->string('destination_centre')->nullable();
            $table->string('vehicle')->nullable();
            $table->text('driver_name')->nullable();
            $table->string('departure')->nullable();
            $table->string('arrival')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippment_batches');
    }
};
