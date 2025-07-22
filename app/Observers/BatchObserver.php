<?php

namespace App\Observers;

use App\Models\ShippmentBatch;

class BatchObserver
{
    /**
     * Handle the ShippmentBatch "created" event.
     */
    public function created(ShippmentBatch $shippmentBatch): void
    {
        //
    }

    /**
     * Handle the ShippmentBatch "updated" event.
     */
    public function updated(ShippmentBatch $shippmentBatch): void
    {
        //
    }

    /**
     * Handle the ShippmentBatch "deleted" event.
     */
    public function deleted(ShippmentBatch $shippmentBatch): void
    {
        //
    }

    /**
     * Handle the ShippmentBatch "restored" event.
     */
    public function restored(ShippmentBatch $shippmentBatch): void
    {
        //
    }

    /**
     * Handle the ShippmentBatch "force deleted" event.
     */
    public function forceDeleted(ShippmentBatch $shippmentBatch): void
    {
        //
    }
}
