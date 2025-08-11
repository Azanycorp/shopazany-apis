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
        Schema::table('shippments', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('shippment_id');
            $table->json('logged_items')->nullable()->after('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shippments', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'logged_items']);
        });
    }
};
