<?php

namespace Database\Seeders;

use App\Models\Action;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Create account',
                'slug' => 'create_an_account',
                'points' => 50,
                'verification_type' => 'automatic',
                'default' => true,
            ],
            [
                'name' => 'Purchase item',
                'slug' => 'purchase_item',
                'points' => 50,
                'verification_type' => 'automatic',
                'default' => true,
            ],
            [
                'name' => 'Product review',
                'slug' => 'product_review',
                'points' => 50,
                'verification_type' => 'automatic',
                'default' => true,
            ],
            [
                'name' => 'Mailing subscribe',
                'slug' => 'mailing_subscribe',
                'points' => 50,
                'verification_type' => 'automatic',
                'default' => true,
            ],
            [
                'name' => 'Referral',
                'slug' => 'referral',
                'points' => 50,
                'verification_type' => 'automatic',
                'default' => true,
            ],
        ];

        foreach ($data as $item) {
            Action::updateOrCreate(
                [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                ],
                [
                    'points' => $item['points'],
                    'verification_type' => $item['verification_type'],
                    'default' => $item['default'],
                ]
            );
        }
    }
}
