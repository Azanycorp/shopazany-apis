<?php

namespace App\Trait;

use App\Models\Order;
use App\Models\B2bOrder;
use App\Models\PickupStation;
use App\Http\Resources\OrderResource;
use App\Http\Resources\B2BOrderResource;
use App\Models\CollationCenter;

trait FindOrders
{
    public function findHubOrder($request)
    {
        $hub = PickupStation::find($request->pickup_id);

        if (! $hub) {
            return $this->error(null, 'Hub not found', 404);
        }

        $order = Order::where('order_no', $request->order_number)->first();

        if ($order) {
            return $this->success(new OrderResource($order), 'Order found successfully.');
        }

        $b2bOrder = B2bOrder::where('order_no', $request->order_number)->first();

        if ($b2bOrder) {
            return $this->success(new B2BOrderResource($b2bOrder), 'Order found successfully.');
        }

        return $this->error(null, 'Order not found.', 404);
    }

    public function findCollationOrder($request)
    {
        $centre = CollationCenter::find($request->collation_id);

        if (! $centre) {
            return $this->error(null, 'Center not found', 404);
        }

        $order = Order::where('order_no', $request->order_number)->first();

        if ($order) {
            return $this->success(new OrderResource($order), 'Order found successfully.');
        }

        $b2bOrder = B2bOrder::where('order_no', $request->order_number)->first();

        if ($b2bOrder) {
            return $this->success(new B2BOrderResource($b2bOrder), 'Order found successfully.');
        }

        return $this->error(null, 'Order not found.', 404);
    }
}
