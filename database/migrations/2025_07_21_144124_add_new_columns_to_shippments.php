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
            $table->string('reciever_name')->nullable();
            $table->string('reciever_phone')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('delivery_address')->nullable();
            $table->text('transfer_reason')->nullable();
            $table->string('destination_hub')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shippments', function (Blueprint $table) {
            $table->dropColumn(['reciever_name', 'vehicle_number','transfer_reason','destination_hub', 'delivery_address', 'reciever_phone']);
        });
    }
};
