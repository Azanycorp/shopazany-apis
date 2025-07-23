<?php

namespace App\Observers;

use App\Models\ShippmentBatch;

class BatchObserver
{
   public function creating(ShippmentBatch $batch)
    {
        $year = date('Y');
        $prefix = "BCH-$year-";

        $last = ShippmentBatch::where('batch_id', 'like', "$prefix%")
            ->lockForUpdate()
            ->orderBy('batch_id', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->batch_id, -3);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        $batch->batch_id = $prefix . $nextNumber;
    }
}
