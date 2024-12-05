<?php

namespace App\Services\B2B;

use App\Enum\ProductStatus;
use App\Enum\RefundRequestStatus;
use App\Models\B2BProduct;
use App\Models\B2BRequestRefund;
use App\Trait\HttpResponse;

class BuyerService
{
    use HttpResponse;

    public function requestRefund($request)
    {
        $complaintNumber = generateRefundComplaintNumber();

        B2BRequestRefund::create([
            'user_id' => $request->user_id,
            'b2b_product_id' => $request->b2b_product_id,
            'complaint_number' => $complaintNumber,
            'order_number' => $request->order_number,
            'type' => $request->type,
            'additional_note' => $request->additional_note,
            'send_reply' => $request->send_reply,
            'status' => RefundRequestStatus::PENDING,
        ]);

        return $this->success(null, 'Request sent successful', 201);
    }

    public function getProducts()
    {
        $products = B2BProduct::where('status', ProductStatus::ACTIVE)
            ->get();

        $data = B2BProductResource::collection($products);

        return $this->success($data, 'Products');
    }
}


