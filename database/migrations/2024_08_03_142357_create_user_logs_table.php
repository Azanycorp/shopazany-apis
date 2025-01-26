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
        Schema::create('user_logs', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('user_type')->nullable();
            $table->string('action');
            $table->longText('description');
            $table->string('url')->nullable();
            $table->string('ip');
            $table->json('device');
            $table->longText('request');
            $table->longText('response');
            $table->dateTime('performed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
