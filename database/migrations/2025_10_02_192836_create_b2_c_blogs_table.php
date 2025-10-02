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
        Schema::create('b2_c_blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('b2_c_blog_category_id')->constrained('b2_c_blog_categories');
            $table->text('short_description');
            $table->longText('description');
            $table->string('image');
            $table->string('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->longText('meta_keywords')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('status');
            $table->integer('created_by');
            $table->timestamps();

            $table->index('b2_c_blog_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2_c_blogs');
    }
};
