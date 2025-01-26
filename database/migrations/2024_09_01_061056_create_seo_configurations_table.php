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
        Schema::create('seo_configurations', function (Blueprint $table): void {
            $table->id();
            $table->longText('keywords');
            $table->longText('description')->nullable();
            $table->string('social_title')->nullable();
            $table->longText('social_description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_configurations');
    }
};
