<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "first_name" => "Test",
                "last_name" => "Test",
                "type" => "b2c_admin",
                "email" => "test@email.com",
                "phone_number" => "0000000000",
                "password" => bcrypt('12345678'),
                "status" => "active"
            ],
            [
                "first_name" => "Test2",
                "last_name" => "Test2",
                "type" => "b2b_admin",
                "email" => "b2badmin@email.com",
                "phone_number" => "0000000000",
                "password" => bcrypt('12345678'),
                "status" => "active"
            ]
        ];

        Admin::insert($data);
    }
}
