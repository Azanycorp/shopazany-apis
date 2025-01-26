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
        Schema::table('rfq_messages', function (Blueprint $table): void {
            //
            $table->unsignedBigInteger('seller_id')->after('preferred_qty')->nullable();
            $table->unsignedBigInteger('buyer_id')->after('seller_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfq_messages', function (Blueprint $table): void {
            //
            $table->unsignedBigInteger('seller_id')->after('preferred_qty')->nullable();
            $table->unsignedBigInteger('buyer_id')->after('seller_id')->nullable();
        });
    }
};
