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
        if (config('database.default') === 'sqlite' || DB::getDriverName() === 'sqlite') {
            return;
        }

        if (! Schema::hasColumn('promo_redemptions', 'id')) {
            Schema::table('promo_redemptions', function (Blueprint $table) {
                $table->id();
            });
        }
    }
};
