<?php

namespace App\Services\B2B;

use App\Models\User;
use App\Models\B2BProduct;
use App\Enum\ProductStatus;
use App\Trait\HttpResponse;
use App\Models\B2BRequestRefund;
use App\Enum\RefundRequestStatus;
use App\Http\Resources\B2BProductResource;
use App\Http\Resources\SellerProfileResource;

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

    public function getProductDetail($slug)
    {
        $product = B2BProduct::where('slug', $slug)->first();

        $moreFromSeller = B2BProduct::select('id', 'name', 'slug', 'category_id', 'description', 'front_image', 'fob_price')
            ->where('user_id', $product->user_id)
            ->where('id','!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

            // $relatedProducts = B2BProduct::select('id', 'name', 'slug', 'category_id', 'description', 'front_image', 'fob_price')
            // ->where('category_id',20)
            // ->where('id', '!=', $product->id)
            // ->inRandomOrder()
            // ->limit(4)
            // ->get();

        $data = new B2BProductResource($product);

        $response = [
            'data' => $data,
            'more_from_seller' => B2BProductResource::collection($moreFromSeller),
           // 'related_products' => B2BProductResource::collection($relatedProducts),
        ];

        return $this->success($response, 'Product Detail');
    }

    public function profile()
    {
        $auth = userAuth();

        $user = User::findOrFail($auth->id);
        $data = new SellerProfileResource($user);

        return $this->success($data, 'Seller profile');
    }

}


