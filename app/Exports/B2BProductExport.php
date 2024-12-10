<?php

namespace App\Exports;

use App\Models\B2BProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class B2BProductExport implements FromCollection, WithHeadings
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
        return B2BProduct::where('user_id', $this->userId)
                ->select('id', 'name', 'description', 'fob_price')
                ->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'FOB Price'
        ];
    }
}
