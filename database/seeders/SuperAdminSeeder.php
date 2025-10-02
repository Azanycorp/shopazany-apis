<?php

namespace Database\Seeders;

use App\Enum\AdminType;
use App\Enum\UserStatus;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@gmail.com',
            'phone_number' => '0123456789',
            'modules' => ['user', 'b2b_admin', 'b2c_admin', 'delivery_management', 'affiliate_admin'],
            'password' => bcrypt('12345678'),
            'type' => AdminType::SUPER_ADMIN,
            'status' => UserStatus::ACTIVE,
        ];

        Admin::create($user);
    }
}
