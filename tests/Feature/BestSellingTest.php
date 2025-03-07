<?php

namespace Tests\Feature;

use App\Enum\OrderStatus;
use App\Models\Action;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BestSellingTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        Action::factory()->create([
            'slug' => 'create_account',
            'points' => 10,
        ]);
    }

    public function testBestSellingProductsAreReturned(): void
    {
        // $headers = [
        //     config('security.header_key', 'X-SHPAZY-AUTH') => config('security.header_value'),
        // ];

        // $product1 = Product::factory()->create(['country_id' => 1]);
        // $product2 = Product::factory()->create(['country_id' => 1]);
        // $product3 = Product::factory()->create(['country_id' => 2]);

        // Order::factory()->create(['status' => OrderStatus::DELIVERED, 'country_id' => 1]);
        // Order::factory()->create(['status' => OrderStatus::DELIVERED, 'country_id' => 1]);
        // Order::factory()->create(['status' => OrderStatus::DELIVERED, 'country_id' => 1]);
        // Order::factory()->create(['status' => OrderStatus::DELIVERED, 'country_id' => 2]);

        // $response = $this->getJson('/api/best/selling', $headers);

        // $response->assertStatus(200)
        //     ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'image', 'price', 'description', 'category_id', 'country_id', 'currency', 'total_orders']]]);

        // $data = $response->json('data');
        // $this->assertEquals($product1->id, $data[0]['id']);
        // $this->assertEquals($product2->id, $data[1]['id']);
        // $this->assertCount(3, $data);

        // $responseWithCountry = $this->getJson('/api/best/selling?country_id=1', $headers);

        // $responseWithCountry->assertStatus(200)
        //     ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'image', 'price', 'description', 'category_id', 'country_id', 'currency', 'total_orders']]]);

        // $filteredData = $responseWithCountry->json('data');
        // $this->assertCount(2, $filteredData);
        // $this->assertEquals($product1->id, $filteredData[0]['id']);
        // $this->assertEquals($product2->id, $filteredData[1]['id']);
    }
}
