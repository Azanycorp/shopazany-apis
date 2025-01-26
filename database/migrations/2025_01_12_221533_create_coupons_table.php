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
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('link');
            $table->integer('max_use')->default(1);
            $table->integer('used')->default(0);
            $table->json('used_by')->nullable();
            $table->string('type');
            $table->dateTime('expire_at');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
