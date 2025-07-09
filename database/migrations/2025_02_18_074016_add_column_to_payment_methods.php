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
        Schema::table('payment_methods', function (Blueprint $table): void {
            $table->dropColumn('swift');
            $table->dropColumn('bank_branch');
            $table->dropColumn('paypal_email');

            $table->renameColumn('account_holder_name', 'account_name');

            $table->string('recipient_code')->nullable()->after('account_name');
            $table->longText('data')->nullable()->after('recipient_code');
        });

        Schema::table('withdrawal_requests', function (Blueprint $table): void {
            $table->string('user_type')->nullable()->after('user_id');
            $table->string('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table): void {
            $table->string('swift')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('paypal_email')->nullable();
            $table->renameColumn('account_name', 'account_holder_name');
            $table->dropColumn('recipient_code');
            $table->dropColumn('data');
        });

        Schema::table('withdrawal_requests', function (Blueprint $table): void {
            $table->dropColumn('user_type');
            $table->string('status')->nullable(false)->change();
        });
    }
};
