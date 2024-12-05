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
        Schema::create('b2_b_request_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->bigInteger('b2b_product_id');
            $table->string('complaint_number');
            $table->string('order_number');
            $table->string('type');
            $table->longText('additional_note');
            $table->boolean('send_reply')->default(0);
            $table->string('status')->default('pending');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2_b_request_refunds');
    }
};
