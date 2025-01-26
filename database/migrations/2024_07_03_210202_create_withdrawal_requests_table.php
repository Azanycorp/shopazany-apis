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
        Schema::create('withdrawal_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('previous_balance', 10, 2);
            $table->decimal('current_balance', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
