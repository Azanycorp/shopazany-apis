<?php

namespace App\Services\B2B;

use App\Models\Rfq;
use App\Models\B2bOrder;
use App\Models\B2bQuote;
use App\Models\B2BProduct;
use App\Enum\ProductStatus;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\B2BRequestRefund;
use App\Enum\RefundRequestStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\B2BProductResource;
use Carbon\Carbon;

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
        $product = B2BProduct::where('slug', $slug)->firstOrFail();

        $moreFromSeller = B2BProduct::select('id', 'name', 'slug', 'category_id', 'description', 'front_image', 'fob_price')
            ->where('user_id', $product->user_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $relatedProducts = B2BProduct::select('id', 'name', 'slug', 'category_id', 'description', 'front_image', 'fob_price')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $data = new B2BProductResource($product);

        $response = [
            'data' => $data,
            'more_from_seller' => B2BProductResource::collection($moreFromSeller),
            'related_products' => B2BProductResource::collection($relatedProducts),
        ];

        return $this->success($response, 'Product Detail');
    }
    //Quotes
    public function allQuotes()
    {
        $quotes = B2bQuote::where([
            'buyer_id' => Auth::user()->id,
        ])->get();
        if (count($quotes) < 1) {
            return $this->error(null, 'No record found to send', 404);
        }
        return $this->success($quotes, 'quotes lists');
    }

    public function sendMutipleQuotes()
    {
        $quotes = B2bQuote::where('buyer_id', Auth::user()->id)->get();
        if (count($quotes) < 1) {
            return $this->error(null, 'No record found to send', 404);
        }
        DB::beginTransaction();
        try {
            if (count($quotes) > 0) {
                foreach ($quotes as $quote) {
                    Rfq::create([
                        'buyer_id' => $quote->buyer_id,
                        'seller_id' => $quote->seller_id,
                        'quote_no' => strtoupper(Str::random(10) . Auth::user()->id),
                        'product_id' => $quote->product_id,
                        'product_quantity' => $quote->qty,
                        'product_data' => json_encode($quote->product_data),
                    ]);
                }
            }

            DB::commit();
            B2bQuote::where('buyer_id', Auth::user()->id)->delete();
            return $this->success(null, 'rfq sent successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'transaction failed, please try again', 500);
        }
    }

    public function sendRfq($id)
    {
        $quote = B2bQuote::where('id', $id)->firstOrFail();
        DB::beginTransaction();
        try {
            Rfq::create([
                'buyer_id' => $quote->buyer_id,
                'seller_id' => $quote->seller_id,
                'quote_no' => strtoupper(Str::random(10) . Auth::user()->id),
                'product_id' => $quote->product_id,
                'product_quantity' => $quote->qty,
                'product_data' => json_encode($quote->product_data),
            ]);
            DB::commit();
            $quote->delete();
            return $this->success(null, 'rfq sent successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'transaction failed, please try again', 500);
        }
    }
    public function sendQuote($data)
    {
        $product = B2BProduct::where('id', $data->product_id)->first();
        $quote = B2bQuote::where('product_id', $data->product_id)->first();
        if ($quote) {
            return $this->error(null, 'Product already exist');
        }
        $quote = B2bQuote::create([
            'buyer_id' => Auth::user()->id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'product_data' => json_encode($product),
            'qty' => $product->minimum_order_quantity,
        ]);

        return $this->success($quote, 'quote Added successfully');
    }


    //dasboard
    public function getDashboardDetails()
    {
        $currentUserId = userAuthId();

        $orders =  B2bOrder::with('buyer')
            ->where('seller_id', $currentUserId)
            ->get();

        $orderStats =  B2bOrder::with('seller')
            ->where([
                'buyer_id' => $currentUserId,
                'created_at' => Carbon::today()->subDays(7)
            ])->where('status','confirmed')->sum('amount');
        $rfqs =  Rfq::with('buyer')->where('seller_id', $currentUserId)->get();

        $data = [
            'total_sales' => $orderStats,
            'rfq_recieved' => $rfqs->count(),
            'rfq_processed' => $rfqs->where('status', 'confirmed')->count(),
            'deals_in_progress' => $orders->where('status', 'in-progress')->count(),
            'deals_in_completed' => $orders->where('status', 'confirmed')->count(),
            'recent_orders' => $orders,
        ];

        return $this->success($data, "Dashboard details");
    }
}
