<?php

namespace App\Services\B2B;

use Carbon\Carbon;
use App\Models\Rfq;
use App\Models\User;
use App\Enum\UserType;
use App\Enum\UserStatus;
use App\Models\B2bOrder;
use App\Models\B2bQuote;
use App\Models\B2BProduct;
use App\Enum\ProductStatus;
use App\Models\B2bWishList;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\B2BRequestRefund;
use App\Enum\RefundRequestStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\B2BProductResource;
use App\Http\Resources\SellerProfileResource;

class BuyerService
{
    use HttpResponse;


    //Admin section
    public function allCustomers()
    {
        $query = trim(request()->input('search'));

        $users = User::where('type',UserType::B2B_BUYER)
        ->where(function($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'LIKE', '%' . $query . '%')
                         ->orWhere('last_name', 'LIKE', '%' . $query . '%')
                         ->orWhere('middlename', 'LIKE', '%' . $query . '%')
                         ->orWhere('email', 'LIKE', '%' . $query . '%');
        })
        ->paginate(25);

        // $data = CustomerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'All Buyers',
            'data' => $users,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'prev_page_url' => $users->previousPageUrl(),
                'next_page_url' => $users->nextPageUrl(),
            ],
        ];
    }

    public function viewCustomer($id)
    {
        $user = User::with(['userCountry', 'state', 'wishlist.product', 'payments.order'])->where('id', $id)
        ->where('type',UserType::B2B_BUYER)
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $data = new CustomerResource($user);

        return [
            'status' => 'true',
            'message' => 'Buyer details',
            'data' => $data,
        ];
    }

    public function banCustomer($request)
    {
        $user = User::where('id', $request->user_id)
        ->where('type',UserType::B2B_BUYER)
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    public function removeCustomer($request)
    {
        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            $user->status = UserStatus::DELETED;
            $user->is_verified = 0;
            $user->is_admin_approve = 0;
            $user->save();

            $user->delete();
        }

        return $this->success(null, "User(s) have been removed successfully");
    }

    public function filter()
    {
        $query = trim(request()->query('approved'));

        $users = User::where('type', 'customer')
            ->when($query !== null, function ($queryBuilder) use ($query) {
                $queryBuilder->where('is_admin_approve', $query);
            })
            ->paginate(25);

        $data = CustomerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'Filter by approval',
            'data' => $data,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'prev_page_url' => $users->previousPageUrl(),
                'next_page_url' => $users->nextPageUrl(),
            ],
        ];
    }

    public function addCustomer($request)
    {
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middlename' => $request->middlename,
                'email' => $request->email,
                'password' => bcrypt('12345678'),
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'type' => UserType::CUSTOMER,
                'is_verified' => 1,
                'is_admin_approve' => 1,
                'status' => $request->status,
            ]);

            $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : null;

            $user->update(['image' => $image]);

        } catch (\Throwable $th) {
            throw $th;
        }

        return $this->success(null, "User has been created successfully");
    }

    public function editCustomer($request)
    {
        $user = User::where('id', $request->user_id)
        ->where('type', 'customer')
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'image' => $image,
            'status' => $request->status,
        ]);

        return $this->success(null, "User has been updated successfully");
    }

    public function getPayment($id)
    {
        $payment = Payment::with(['user', 'order'])->findOrFail($id);
        $data = new PaymentResource($payment);

        return $this->success($data, "Payment detail");
    }


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
        $products = B2BProduct::with(['category', 'subCategory', 'country', 'b2bProductImages'])->where('status', ProductStatus::ACTIVE)
            ->get();

        $data = B2BProductResource::collection($products);

        return $this->success($data, 'Products');
    }


    public function getProductDetail($slug)
    {
        $product = B2BProduct::with(['category', 'country', 'b2bProductImages'])
            ->where('slug', $slug)
            ->firstOrFail();

        $moreFromSeller = B2BProduct::with(['category', 'country', 'b2bProductImages'])->where('user_id', $product->user_id);

        $relatedProducts = B2BProduct::with(['category', 'country', 'b2bProductImages'])->where('category_id', $product->category_id);


        $data = new B2BProductResource($product);

        $response = [
            'data' => $data,
            'more_from_seller' => B2BProductResource::collection($moreFromSeller),
            'related_products' => B2BProductResource::collection($relatedProducts),
        ];

        return $this->success($response, 'Product Details');
    }



    //Quotes
    public function allQuotes()
    {
        $quotes = B2bQuote::where('buyer_id', Auth::user()->id)->latest('id')->get();
        if (count($quotes) < 1) {
            return $this->error(null, 'No record found to send', 404);
        }
        return $this->success($quotes, 'quotes lists');
    }

    public function sendMutipleQuotes()
    {
        $quotes = B2bQuote::where('buyer_id', Auth::user()->id)->latest('id')->get();
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
                        'total_amount' => ($quote->product_data['unit_price'] * $quote->product_data['minimum_order_quantity']),
                        'p_unit_price' => $quote->product_data['unit_price'],
                        'product_data' => $quote->product_data,
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
        $quote = B2bQuote::where('id', $id)->first();
        if (!$quote) return $this->error(null, 'No record found', 404);
        DB::beginTransaction();
        try {
            $amount = ($quote->product_data['unit_price'] * $quote->product_data['minimum_order_quantity']);
            Rfq::create([
                'buyer_id' => $quote->buyer_id,
                'seller_id' => $quote->seller_id,
                'quote_no' => strtoupper(Str::random(10) . Auth::user()->id),
                'product_id' => $quote->product_id,
                'product_quantity' => $quote->qty,
                'total_amount' => $amount,
                'p_unit_price' => $quote->product_data['unit_price'],
                'product_data' => $quote->product_data,
            ]);
            DB::commit();
            $quote->delete();
            return $this->success(null, 'rfq sent successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'transaction failed, please try again', 500);
        }
    }

    public function removeQuote($id)
    {
        $quote = B2bQuote::where('id', $id)->first();
        if (!$quote) return $this->error(null, 'No record found', 404);
        $quote->delete();
        return $this->success(null, 'Item removed successfully');
    }
    public function sendQuote($data)
    {
        $product = B2BProduct::where('id', $data->product_id)->first();
        $quote = B2bQuote::where('product_id', $data->product_id)->first();
        if ($quote) {
            return $this->error(null, 'Product already exist');
        }
        if ($data->qty < $product->minimum_order_quantity) {
            return $this->error(null, 'Your peferred quantity can not be less than the one already set', 422);
        }
        $quote = B2bQuote::create([
            'buyer_id' => Auth::user()->id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'product_data' => $product,
            'qty' => $data->qty,
        ]);

        return $this->success($quote, 'quote Added successfully');
    }


    //dasboard
    public function getDashboardDetails()
    {
        $currentUserId = userAuthId();
        $orders =  Rfq::where('buyer_id', $currentUserId)->get();
        $orderStats =  Rfq::with('seller')
            ->where([
                'buyer_id' => $currentUserId,
                'status' => 'confirmed'
            ])->where('created_at', '<=', Carbon::today()->addDays(7))->sum('total_amount');
        $rfqs =  Rfq::with('buyer')->where('buyer_id', $currentUserId)->get();
        $orderCounts = DB::table('rfqs')
            ->select('seller_id', DB::raw('COUNT(*) as total_orders'))
            ->groupBy('id')
            ->where('buyer_id', $currentUserId)
            ->count();

        $data = [
            'total_sales' => $orderStats,
            'partners' => $orderCounts,
            'rfq_sent' => $rfqs->count(),
            'rfq_accepted' => $rfqs->where('status', '!=', 'pending')->count(),
            'deals_in_progress' => $orders->where('status', 'in-progress')->count(),
            'deals_in_completed' => $orders->where('status', 'delivered')->count(),
            'recent_orders' => $orders,
        ];

        return $this->success($data, "Dashboard details");
    }
    public function allRfqs()
    {
        $rfqs = Rfq::with('seller')->where('buyer_id', Auth::user()->id)->latest('id')->get();
        if (count($rfqs) < 1) {
            return $this->error(null, 'No record found to send', 404);
        }
        return $this->success($rfqs, 'rfqs lists');
    }

    public function rfqDetails($id)
    {
        $rfq = Rfq::with(['seller', 'messages'])->find($id);
        // return $rfq->seller->first_name;
        if (!$rfq) {
            return $this->error(null, 'No record found to send', 404);
        }
        return $this->success($rfq, 'rfq details');
    }

    //send review request to vendor
    public function sendReviewRequest($data)
    {
        $rfq = Rfq::find($data->rfq_id);
        if (!$rfq) {
            return $this->error(null, 'No record found to send', 404);
        }

        $rfq->messages()->create([
            'rfq_id' => $data->rfq_id,
            'p_unit_price' => $data->p_unit_price,
            'preferred_qty' => $rfq->qty,
            'note' => $data->note
        ]);
        $rfq->update([
            'status' => 'review'
        ]);
        return $this->success($rfq, 'Review sent successfully details');
    }
    //send review request to vendor
    public function acceptQuote($data)
    {
        $rfq = Rfq::find($data->rfq_id);
        if (!$rfq) {
            return $this->error(null, 'No record found to send', 404);
        }
        $rfq->update([
            'status' => 'in-progress',
        ]);
        return $this->success($rfq, 'Quote Accepted successfully');
    }

    public function addToWishList($data)
    {
        $product = B2BProduct::find($data->product_id);
        if (!$product) {
            return $this->error(null, 'No record found to send', 404);
        }
        B2bWishList::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id
        ]);
        return $this->success(null, 'Product Added successfully');
    }
    //wish list
    public function myWishList()
    {
        $wishes =  B2bWishList::with('product')->where('user_id', Auth::id())->latest('id')->get();
        if (count($wishes) < 1) {
            return $this->error(null, 'No record found to send', 404);
        }
        return $this->success($wishes, 'My Wish List');
    }

    public function removeItem($id)
    {
        $wish =  B2bWishList::find($id);
        if (!$wish) {
            return $this->error(null, 'No record found to send', 404);
        }
        $wish->delete();
        return $this->success(null, 'Item Removed');
    }
    //Account section
    public function profile()
    {
        $auth = userAuth();

        $user = User::with('b2bCompany')->where('id', $auth->id)->where('type',UserType::B2B_BUYER)->first();
        if (!$user) return $this->error(null, 'User does not exist');
        // $data = new SellerProfileResource($user);

        return $this->success($user, 'Buyer profile');
    }

    public function editAccount($request)
    {
        $user = Auth::user();
        $image = $request->hasFile('logo') ? uploadUserImage($request, 'logo', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone,
            'image' => $image
        ]);

        return $this->success(null, "Profile Updated successfully");
    }

    public function changePassword($request)
    {
        $user = $request->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);

            return $this->success(null, 'Password Successfully Updated');
        } else {
            return $this->error(null, 422, 'Old Password did not match');
        }
    }

    public function editCompany($request)
    {
        $user = Auth::user();
        $user->b2bCompany()->update([
            'company_name' => $request->company_name ?? $user->company_name,
            'company_size' => $request->company_size ?? $user->company_size,
            'website' => $request->website ?? $user->website,
            'average_spend' => $request->average_spend ?? $user->average_spend,
            'service_type' => $request->service_type ?? $user->service_type,
            'country_id' => $request->country_id ?? $user->country,
        ]);
        return $this->success(null, "Details Updated successfully");
    }
}
