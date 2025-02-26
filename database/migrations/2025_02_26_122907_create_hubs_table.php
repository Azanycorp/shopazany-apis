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
        Schema::create('hubs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collation_center_id');
            $table->string('name');
            $table->string('location');
            $table->string('status')->default('active');
            $table->text('note')->nullable();
            $table->text('city')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hubs');
    }
};
