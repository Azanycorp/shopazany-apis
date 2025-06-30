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
        Schema::table('b2b_product_categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('b2b_product_categories', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
