<?php

namespace App\Imports;

use App\Models\B2BProduct;
use App\Enum\ProductStatus;
use Illuminate\Support\Str;
use App\Models\B2bProductCategory;
use Illuminate\Support\Collection;
use App\Models\B2bProductSubCategory;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class B2BProductImport implements ToCollection, WithHeadingRow, WithChunkReading, WithBatchInserts, SkipsEmptyRows
{
    protected $seller;

    public function __construct($seller)
    {
        $this->seller = $seller;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $category = $this->getCategory($row['category']);
            $subCategory = $this->getSubCategory($row['sub_category']);

            $slug = Str::slug($row['product_name']);

            if (B2BProduct::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }

            B2BProduct::create([
                'user_id' => $this->seller->id,
                'name' => $row['product_name'],
                'slug' => $slug,
                'description' => $row['description'],
                'category_id' => $category ? $category->id : 1,
                'sub_category_id' => $subCategory ? $subCategory->id : 1,
                'fob_price' => $row['price'],
                'unit_price' => $row['price'],
                'minimum_order_quantity' => $row['minimum_order_quantity'],
                'keywords' => $row['keywords'],
                'quantity' => $row['quantity'],
                'availability_quantity' => $row['quantity'],
                'front_image' => 'https://azany-uploads.s3.amazonaws.com/stag/b2bproduct/nedu24/front_image/pVCPKKfe655Yspt0WfOt3m7ThSsRbPhNGeWLbMlY.png',
                'status' => ProductStatus::PENDING,
                'country_id' => $this->seller?->country,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getCategory($category)
    {
        $getCategory = B2bProductCategory::where('name', $category)->first();

        if(!$getCategory){
            $category = null;

        } else {
            $category = $getCategory;
        }

        return $category;
    }

    private function getSubCategory($subCategory)
    {
        $getSubCategory = B2bProductSubCategory::where('name', $subCategory)->first();

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
