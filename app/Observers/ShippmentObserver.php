<?php


namespace App\Observers;

use App\Models\Shippment;
use Illuminate\Support\Facades\DB;

class ShippmentObserver
{
    public function creating(Shippment $shipment)
    {
        $year = date('Y');
        $prefix = "SHP-$year-";

        $last = Shippment::where('shippment_id', 'like', "$prefix%")
            ->lockForUpdate()
            ->orderBy('shippment_id', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->shippment_id, -3);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        $shipment->shippment_id = $prefix . $nextNumber;
    }
}

