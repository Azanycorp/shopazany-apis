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
        Schema::table('user_subcriptions', function (Blueprint $table): void {
            $table->string('subscription_type')->nullable()->after('authorization_data');
            $table->longText('authorization_data')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subcriptions', function (Blueprint $table): void {
            $table->dropColumn('subscription_type');
            $table->longText('authorization_data')->change();
        });
    }
};
