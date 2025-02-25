<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Enum\ProductStatus;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\SubCategory;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AllProductsTest extends TestCase
{
    use RefreshDatabase;

    public function testAllProductsAreReturned(): void
    {
        $headers = [
            config('security.header_key', 'X-SHPAZY-AUTH') => config('security.header_value'),
        ];

        $category = Category::factory()->create();
        $subCategory = SubCategory::factory()->create();
        $brand = Brand::factory()->create();
        $color = Color::factory()->create();
        $unit = Unit::factory()->create();
        $size = Size::factory()->create();

        $activeProduct1 = Product::factory()->create([
            'status' => ProductStatus::ACTIVE,
            'country_id' => 1,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'brand_id' => $brand->id,
            'color_id' => $color->id,
            'unit_id' => $unit->id,
            'size_id' => $size->id
        ]);

        $activeProduct2 = Product::factory()->create([
            'status' => ProductStatus::ACTIVE,
            'country_id' => 2,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'brand_id' => $brand->id,
            'color_id' => $color->id,
            'unit_id' => $unit->id,
            'size_id' => $size->id
        ]);

        ProductImage::factory()->create(['product_id' => $activeProduct1->id]);
        ProductImage::factory()->create(['product_id' => $activeProduct2->id]);

        $response = $this->getJson('/api/all/products', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'category' => [
                            'category_id',
                            'category_name',
                            'sub_category_id',
                            'sub_category_name',
                        ],
                        'brand_id',
                        'color_id',
                        'unit_id',
                        'size_id',
                        'brand',
                        'color',
                        'unit',
                        'size',
                        'product_sku',
                        'product_price',
                        'discount_price',
                        'price',
                        'current_stock_quantity',
                        'minimum_order_quantity',
                        'front_image',
                        'images' => [
                            '*' => ['image']
                        ],
                        'currency',
                        'country_id',
                        'status'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'prev_page_url',
                    'next_page_url'
                ]
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        $this->assertEquals($activeProduct1->id, $data[0]['id']);
        $this->assertEquals($activeProduct2->id, $data[1]['id']);
        $this->assertEquals($category->id, $data[0]['category']['category_id']);
        $this->assertEquals($subCategory->id, $data[0]['category']['sub_category_id']);

        $responseWithCountry = $this->getJson('/api/all/products?country_id=1', $headers);

        $responseWithCountry->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'category',
                        'brand_id',
                        'color_id',
                        'unit_id',
                        'size_id',
                        'product_sku',
                        'product_price',
                        'discount_price',
                        'price',
                        'current_stock_quantity',
                        'minimum_order_quantity',
                        'front_image',
                        'images',
                        'currency',
                        'country_id',
                        'status'
                    ]
                ],
                'pagination'
            ]);

        $filteredData = $responseWithCountry->json('data');
        $this->assertCount(1, $filteredData);
        $this->assertEquals($activeProduct1->id, $filteredData[0]['id']);
    }
}
