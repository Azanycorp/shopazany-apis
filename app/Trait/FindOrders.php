<?php

namespace App\Trait;

use App\Models\Order;
use App\Models\B2bOrder;
use App\Models\Shippment;
use Illuminate\Support\Str;
use App\Models\PickupStation;
use App\Enum\ShippmentCategory;
use App\Models\CollationCenter;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Trait\SuperAdminNotification;
use App\Http\Resources\B2BOrderResource;
use App\Http\Resources\ShippmentResource;
use App\Http\Resources\SearchB2BOrderResource;

trait FindOrders
{
    use SuperAdminNotification;
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
            $resource = new SearchB2BOrderResource($b2bOrder);
            $array = $resource->toArray(request());

            $items = $array['product_quantity'];
            $vendor = $array['vendor'];
            $package = $array['product'];
            $customer = $array['customer'];
            $shippment = Shippment::create([
                'collation_id' => $hub->id,
                'shippment_id' => Str::random(20),
                'type' => ShippmentCategory::INCOMING,
                'package' => $package,
                'customer' => $customer,
                'vendor' => $vendor,
                'status' => $request->status,
                'priority' => $request->priority,
                'expected_delivery_date' => $request->expected_delivery_date,
                'start_origin' => $hub->name,
                'items' => $items,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->note
            ]);

            $this->createNotification('New Shippment created', 'New Shippment created at ' . $hub->name . 'Pickup station/hub ' . 'by ' . Auth::user()->fullName);

            return $this->success(new ShippmentResource($shippment), 'Item Logged successfully.');
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
            $package = $array['product'];
            $customer = $array['customer'];
            $shippment = Shippment::create([
                'collation_id' => $centre->id,
                'shippment_id' => Str::random(20),
                'type' => ShippmentCategory::INCOMING,
                'package' => $package,
                'customer' => $customer,
                'vendor' => $vendor,
                'status' => $request->status,
                'priority' => $request->priority,
                'expected_delivery_date' => $request->expected_delivery_date,
                'start_origin' => $centre->name,
                'items' => $items,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->note
            ]);

            $this->createNotification('New Shippment created', 'New Shippment created at ' . $centre->name . 'Collation centre ' . 'by ' . Auth::user()->fullName);

            return $this->success(new ShippmentResource($shippment), 'Item Logged successfully.');
        }

        return $this->error(null, 'Order not found.', 404);
    }
}
