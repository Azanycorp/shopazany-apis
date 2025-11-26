<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements SkipsEmptyRows, ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    protected $seller;

    public function __construct($seller)
    {
        $this->seller = $seller;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $category = $this->getCategory($row['category']);
            $subCategory = $this->getSubCategory($row['sub_category']);

            $slug = Str::slug($row['product_name']);

            if (Product::where('slug', $slug)->exists()) {
                $slug = $slug.'-'.uniqid();
            }

            Product::create([
                'user_id' => $this->seller->id,
                'name' => $row['product_name'],
                'slug' => $slug,
                'description' => $row['description'],
                'category_id' => $category ? $category->id : 1,
                'sub_category_id' => $subCategory ? $subCategory->id : 1,
                'product_price' => $row['price'],
                'price' => $row['price'],
                'current_stock_quantity' => $row['stock_quantity'],
                'minimum_order_quantity' => $row['minimum_order_quantity'],
                'status' => 'pending',
                'added_by' => $this->seller?->type,
                'country_id' => $this->seller?->country,
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ]);
        }
    }

    private function getCategory($category)
    {
        $getCategory = Category::where('name', $category)->first();

        return $getCategory ? $getCategory : null;
    }

    private function getSubCategory($subCategory)
    {
        $getSubCategory = SubCategory::where('name', $subCategory)->first();

        return $getSubCategory ? $getSubCategory : null;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
