<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateCountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restrictedCountries = [
            'Cuba', 'Iran', 'Libya', 'North Korea', 'Syria', 'Belarus',
            'Central African Republic', 'Democratic Republic of Congo', 'Iraq',
            'Lebanon', 'Burma', 'China', 'Nicaragua', 'Russia',
            'Russian sanctions', 'Sudan', 'Venezuela',
        ];

        DB::table('countries')
            ->whereIn('name', $restrictedCountries)
            ->update(['is_allowed' => 0]);

        $this->command->info('Restricted countries updated successfully.');
    }
}
