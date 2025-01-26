<?php

namespace App\Exports;

use App\Models\B2BProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class B2BProductExport implements FromCollection, WithHeadings
{
    protected $userId,$data;
    public function __construct($userId,$data)
    {
        $this->userId = $userId;
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return B2BProduct::where('user_id', $this->userId)
                ->select('id', 'name','description', 'unit_price','availability_quantity','quantity')
                ->whereBetween('created_at', [$this->data->startDate, $this->data->endDate])
                ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Unit_price',
            'Availability_quantity',
            'Quantity'
        ];
    }
}
