<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SuperAdminService
{
    public function seedRun()
    {
        $seederClass = Str::studly(request()->input('seeder_class'));

        if (!class_exists("Database\\Seeders\\{$seederClass}")) {
            return response()->json([
                'error' => "Seeder class '{$seederClass}' not found in Database\\Seeders namespace."
            ], 404);
        }

        try {
            Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);

            return response()->json([
                'message' => "{$seederClass} executed successfully.",
                'output' => Artisan::output(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Seeder failed to run.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
