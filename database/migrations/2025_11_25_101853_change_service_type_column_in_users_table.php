<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE users
            SET service_type = JSON_QUOTE(service_type)
            WHERE service_type IS NOT NULL
            AND JSON_VALID(service_type) = 0
        ');

        Schema::table('users', function (Blueprint $table) {
            $table->json('service_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('service_type')->change();
        });
    }
};
