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
        Schema::table('products', function (Blueprint $table): void {
            $table->string('public_id')->nullable()->after('image');
        });

        Schema::table('product_images', function (Blueprint $table): void {
            $table->string('public_id')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('public_id');
        });

        Schema::table('product_images', function (Blueprint $table): void {
            $table->dropColumn('public_id');
        });
    }
};
