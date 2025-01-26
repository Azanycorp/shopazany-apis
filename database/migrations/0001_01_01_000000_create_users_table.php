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
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('referrer_code')->nullable();
            $table->string('referrer_link')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->tinyInteger('is_verified')->nullable();
            $table->enum('income_type', ['payment', 'commision'])->default('payment');
            $table->string('image')->nullable();
            $table->boolean('is_affiliate_member')->default(0);
            $table->string('login_code')->nullable();
            $table->dateTime('login_code_expires_at')->nullable();
            $table->enum('status', ['pending', 'active', 'blocked', 'suspended', 'deleted'])->default('pending');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
