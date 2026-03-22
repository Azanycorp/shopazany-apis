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
        Schema::table('user_business_information', function (Blueprint $table) {
            $table->string('business_logo')->nullable();
            $table->string('business_banner')->nullable();
            $table->integer('min_order_amount')->default(1);
            $table->string('opening_time')->nullable();
            $table->string('closing_time')->nullable();
            $table->integer('estimated_delivery_days')->nullable();
            $table->string('order_prefix')->nullable();
            $table->longText('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_business_information', function (Blueprint $table) {
            $table->dropColumn(['business_logo', 'business_banner', 'min_order_amount', 'opening_time', 'closing_time', 'estimated_delivery_days', 'order_prefix', 'description']);
        });
    }
};
