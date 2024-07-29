<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow, WithChunkReading, WithBatchInserts, SkipsEmptyRows
{
    protected $sellerId;

    public function __construct($sellerId)
    {
        $this->sellerId = $sellerId;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            $category = $this->getCategory($row['category']);
            $subCategory = $this->getSubCategory($row['sub_category']);

            Product::create([
                'user_id' => $this->sellerId,
                'name' => $row['product_name'],
                'description' => $row['description'],
                'category_id' => $category ? $category->id : 1,
                'sub_category_id' => $subCategory ? $subCategory->id : 1,
                'product_price' => $row['price'],
                'price' => $row['price'],
                'current_stock_quantity' => $row['stock_quantity'],
                'minimum_order_quantity' => $row['minimum_order_quantity'],
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }

    private function getCategory($category)
    {
        $getCategory = Category::where('name', $category)->first();

        if(!$getCategory){
            $category = null;

        } else {
            $category = $getCategory;
        }

        return $category;
    }

    private function getSubCategory($subCategory)
    {
        $getSubCategory = SubCategory::where('name', $subCategory)->first();

        if(!$getSubCategory){
            $subCategory = null;

        } else {
            $subCategory = $getSubCategory;
        }

        return $subCategory;
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
