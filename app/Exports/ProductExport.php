<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
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
        return Product::where('user_id', $this->userId)
            ->select('id', 'name', 'description', 'price')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Price',
        ];
    }
}
