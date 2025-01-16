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
        Schema::table('admins', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $table->string('type')->after('password')->comment('Handeles admin user type eg b2c,b2b for b2b_admin admin user and b2c_admin admin user')->change();

                $table->integer('two_factor_enabled')->after('type')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            //
        });
    }
};
