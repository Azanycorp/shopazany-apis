<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductExport implements FromCollection
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::where('user_id', $this->userId)->get();
    }
}
