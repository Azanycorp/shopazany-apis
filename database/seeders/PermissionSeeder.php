<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'overview'
            ],
            [
                'name' => 'order_management'
            ],
            [
                'name' => 'seller_management'
            ],
            [
                'name' => 'customer_management'
            ],
            [
                'name' => 'banner_promo'
            ],
            [
                'name' => 'points_management'
            ],
            [
                'name' => 'add_new_product'
            ],
            [
                'name' => 'product_list'
            ],
            [
                'name' => 'pending_products'
            ],
            [
                'name' => 'seller_products'
            ],
            [
                'name' => 'azany_products'
            ],
            [
                'name' => 'categories'
            ],
            [
                'name' => 'advert_requests'
            ],
            [
                'name' => 'manage_blogs'
            ],
            [
                'name' => 'manage_policy_pages'
            ],
            [
                'name' => 'subscriptions'
            ],
            [
                'name' => 'roles_permissions'
            ],
            [
                'name' => 'category_create'
            ],
            [
                'name' => 'sub_category_create'
            ],
            [
                'name' => 'sub_category'
            ],
            [
                'name' => 'category_featured_status'
            ],
        ];

        Permission::insert($data);
    }
}
