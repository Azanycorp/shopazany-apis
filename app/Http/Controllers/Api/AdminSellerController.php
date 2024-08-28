<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\SellerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AdminSellerController extends Controller
{
    protected $service;

    public function __construct(SellerService $service)
    {
        $this->service = $service;
    }

    public function allSellers()
    {
        return $this->service->allSellers();
    }

    public function approveSeller(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        return $this->service->approveSeller($request);
    }

    public function viewSeller($id)
    {
        return $this->service->viewSeller($id);
    }

    public function editSeller(Request $request, $id)
    {
        $request->validate([
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'email_address' => ['email', 'email:rfc,dns', 'unique:users,email'],
            'phone_number' => ['string'],
            'password' => ['string', 'confirmed', Password::defaults()],
        ]);

        return $this->service->editSeller($request, $id);
    }

    public function banSeller(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        return $this->service->banSeller($request);
    }

    public function removeSeller($id)
    {
        return $this->service->removeSeller($id);
    }

    public function filter()
    {
        return $this->service->filter();
    }

    public function search()
    {
        return $this->service->search();
    }
}