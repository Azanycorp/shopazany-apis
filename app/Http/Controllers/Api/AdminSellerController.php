<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\SellerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;

class AdminSellerController extends Controller
{
    protected \App\Services\Admin\SellerService $service;

    const MESSAGE = '403 Forbidden';

    public function __construct(SellerService $service)
    {
        $this->service = $service;
    }

    public function allSellers(): array
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->allSellers();
    }

    public function approveSeller(Request $request)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        return $this->service->approveSeller($request);
    }

    public function viewSeller($id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->viewSeller($id);
    }

    public function editSeller(Request $request, $id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'email_address' => ['email', 'email:rfc,dns'],
            'phone_number' => ['string'],
            'password' => ['string', 'confirmed', Password::defaults()],
        ]);

        return $this->service->editSeller($request, $id);
    }

    public function banSeller(Request $request)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        return $this->service->banSeller($request);
    }

    public function removeSeller($id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->removeSeller($id);
    }

    public function paymentHistory($id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->paymentHistory($id);
    }

    public function bulkRemove(Request $request)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        return $this->service->bulkRemove($request);
    }
}
