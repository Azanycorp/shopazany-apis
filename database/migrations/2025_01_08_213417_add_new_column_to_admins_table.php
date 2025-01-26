<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            if (DB::connection()->getDriverName() === 'mysql' && !Schema::hasColumn('admins', 'type')) {
                $table->enum('type', ['b2b_admin', 'b2c_admin'])->default('b2c_admin')->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }
};
