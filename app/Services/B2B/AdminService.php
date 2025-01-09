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

class AdminService
{
    use HttpResponse;

    //orders

    public function getAllRfq()
    {
        $rfqs =  Rfq::with(['buyer', 'seller'])->get();

        if (count($rfqs)) {
            return $this->error(null, "No record found.", 404);
        }

        return $this->success($rfqs, "rfqs");
    }

    public function getRfqDetails($id)
    {
        $order = Rfq::with('messages')
            ->where('id', $id)
            ->find($id);

        if (!$order) {
            return $this->error(null, "No record found.", 404);
        }
        
        return $this->success($order, "Rfq details");
    }
}
