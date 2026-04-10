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
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('url');
            $table->string('route')->nullable();
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->integer('status_code')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->float('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
