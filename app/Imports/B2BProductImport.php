<?php

namespace App\Imports;

use App\Enum\ProductStatus;
use App\Models\B2BProduct;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
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
                'price' => $row['price'],
                'minimum_order_quantity' => $row['minimum_order_quantity'],
                'status' => ProductStatus::PENDING,
                'country_id' => $this->seller?->country,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
