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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('service_type')->nullable();
            $table->string('average_spend')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_size')->nullable();
            $table->string('website')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('service_type');
            $table->dropColumn('average_spend');
            $table->dropColumn('company_name');
            $table->dropColumn('company_size');
            $table->dropColumn('website');
        });
    }
};
