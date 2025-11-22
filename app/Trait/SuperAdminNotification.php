<?php

namespace App\Trait;

use App\Enum\ShippmentCategory;
use App\Models\AdminNotification;
use App\Models\Shippment;

trait SuperAdminNotification
{
    public function createNotification($title, $content): void
    {
        AdminNotification::create([
            'title' => $title,
            'content' => $content,
        ]);
    }

    protected function logB2CShipment($request, $order, $hub, $loggedItems, $orderNumber)
    {
        $itemsCount = $order->products->sum(fn ($p) => (int) $p->pivot->product_quantity);

        $seller = $order->products->first()?->user;

        $vendor = $seller ? [
            'business_name' => $seller->company_name,
            'contact' => $seller->phone,
            'location' => $seller->address,
        ] : null;

        $package = $order->products->map(fn ($product) => [
            'name' => $product->name,
            'quantity' => $product->pivot->product_quantity,
            'price' => $product->pivot->price,
            'sub_total' => $product->pivot->sub_total,
            'image' => $product->image,
        ])->values()->toArray();

        $customerUser = $order->user;

        $customer = $customerUser ? [
            'first_name' => $customerUser->first_name,
            'last_name' => $customerUser->last_name,
            'email' => $customerUser->email,
            'address' => $customerUser->address,
        ] : null;

        $shipment = Shippment::create([
            'hub_id' => $hub->id,
            'type' => ShippmentCategory::INCOMING,
            'order_number' => $orderNumber,
            'shippment_id' => generateShipmentId(),
            'package' => $package,
            'customer' => $customer,
            'item_condition' => $request->item_condition,
            'vendor' => $vendor,
            'status' => $request->status,
            'priority' => $request->priority,
            'expected_delivery_date' => $request->expected_delivery_date,
            'start_origin' => $hub->name,
            'items' => $itemsCount,
            'logged_items' => $loggedItems,
        ]);

        return ['shipment' => $shipment];
    }

    protected function logB2BShipment($request, $b2bOrder, $hub, $loggedItems, $orderNumber)
    {
        $itemsCount = (string) $b2bOrder->product_quantity;

        $package = (new \Illuminate\Support\Collection($b2bOrder->product_data ?? []))->only(['name', 'fob_price', 'front_image']);

        $businessInfo = $b2bOrder->seller?->businessInformation;
        $vendor = $businessInfo ? (object) [
            'business_name' => $businessInfo->business_name,
            'contact' => $businessInfo->business_phone,
            'location' => $businessInfo->business_location,
        ] : null;

        $buyer = $b2bOrder->buyer;
        $customer = $buyer ? (object) [
            'name' => $buyer->fullName,
            'email' => $buyer->email,
            'phone' => $buyer->phone,
            'city' => $buyer->city,
            'address' => $buyer->address,
        ] : null;

        $shipment = Shippment::create([
            'hub_id' => $hub->id,
            'type' => ShippmentCategory::INCOMING,
            'order_number' => $orderNumber,
            'shippment_id' => generateShipmentId(),
            'package' => $package,
            'customer' => $customer,
            'vendor' => $vendor,
            'status' => $request->status,
            'priority' => $request->priority,
            'expected_delivery_date' => $request->expected_delivery_date,
            'start_origin' => $hub->name,
            'items' => $itemsCount,
            'logged_items' => $loggedItems,
        ]);

        return ['shipment' => $shipment];
    }
}
