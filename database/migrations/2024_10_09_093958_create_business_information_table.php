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
        Schema::create('business_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('business_location');
            $table->string('business_type');
            $table->string('business_name')->nullable();
            $table->string('business_reg_number')->nullable();
            $table->string('business_phone')->nullable();
            $table->integer('country_id')->nullable();
            $table->string('city')->nullable();
            $table->longText('address')->nullable();
            $table->string('zip')->nullable();
            $table->string('state')->nullable();
            $table->string('apartment')->nullable();
            $table->string('business_reg_document')->nullable();
            $table->string('identification_type');
            $table->string('identification_type_document');
            $table->boolean('agree')->default(0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_information');
    }
};
