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
        DB::table('users')->get()->each(function ($user) {
            $json = $user->referrer_link
                ? json_encode($user->referrer_link)
                : json_encode([]);

            DB::table('users')->where('id', $user->id)->update([
                'referrer_link' => $json,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('referrer_link')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referrer_link')->change();
        });
    }
};
