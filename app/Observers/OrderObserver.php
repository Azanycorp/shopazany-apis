<?php

namespace App\Observers;

use App\Mail\CustomerOrderMail;
use App\Mail\SellerOrderMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Mail;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // if ($order->user_id) {
        //     $user = User::find($order->user_id);
        //     if ($user) {
        //         Mail::to($user->email)->send(new CustomerOrderMail($user, $order));
        //         //defer(fn() => send_email($user->email, new CustomerOrderMail($user, $order)));
        //     }
        // }

        // if ($order->seller_id) {
        //     $seller = User::find($order->seller_id);
        //     if ($seller) {
        //         Mail::to($seller->email)->send(new SellerOrderMail($seller, $order));
        //     }
        // }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
