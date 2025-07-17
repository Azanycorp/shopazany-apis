<?php

namespace App\Trait;

use App\Models\Order;
use App\Models\B2bOrder;
use App\Models\Shippment;
use Illuminate\Support\Str;
use App\Models\PickupStation;
use App\Models\CollationCenter;
use App\Http\Resources\OrderResource;
use App\Http\Resources\B2BOrderResource;
use App\Http\Resources\SearchB2BOrderResource;

trait FindOrders
{
    public function searchOrder()
    {

        $order = Order::where('order_no', request()->order_number)->first();

        if ($order) {
            return $this->success(new OrderResource($order), 'Order found successfully.');
        }

        $b2bOrder = B2bOrder::where('order_no', request()->order_number)->first();

        if ($b2bOrder) {
            return $this->success(new SearchB2BOrderResource($b2bOrder));
        }

        return $this->error(null, 'Order not found.', 404);
    }

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
            $resource = new SearchB2BOrderResource($b2bOrder);
            $array = $resource->toArray(request());

            $items = $array['product_quantity'];
            $vendor = $array['vendor'];
            return $package = $array['product_data']->only('id','user_id');
            $customer = $array['customer'];
            $shippment = Shippment::create([
                'collation_id' => $centre->id,
                'shippment_id' => Str::random(20),
                'package' => $package,
                'customer' => $customer,
                'vendor' => $vendor,
                'status' => $request->status,
                'priority' => $request->priority,
                'expected_delivery_date' => $request->expected_delivery_date,
                'start_origin' => $centre->name,
                'activity' => $request->activity,
                'note' => $request->note,
                'items' => $items,
            ]);

            $shippment->activities()->create([
                'action' => $request->activity
            ]);
            return $this->success(null, 'Item Logged successfully.');
        }

        return $this->error(null, 'Order not found.', 404);
    }
}
