<?php

namespace App\Http\Controllers\Api;

use App\Models\B2BProduct;
use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\AddProductRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\B2B\UpdateProductRequest;
use App\Http\Requests\B2B\SellerShippingRequest;
use App\Http\Requests\B2B\WithdrawalMethodRequest;

class B2BSellerController extends Controller
{
    protected \App\Services\B2B\SellerService $service;

    public function __construct(SellerService $service)
    {
        $this->service = $service;
    }

    public function productImport(Request $request)
    {
        return $this->service->b2bproductImport($request);
    }

    public function export(Request $request, $userId)
    {
        return $this->service->exportSellerProduct($request, $userId);
    }

    public function profile()
    {
        return $this->service->profile();
    }

    public function editAccount(Request $request)
    {
        return $this->service->editAccount($request);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->service->changePassword($request);
    }

    public function editCompany(Request $request)
    {
        return $this->service->editCompany($request);
    }

    public function addProduct(AddProductRequest $request)
    {
        return $this->service->addProduct($request);
    }

    public function getAllProduct(Request $request)
    {
        return $this->service->getAllProduct($request);
    }

    public function getProductById($product_id, $user_id)
    {
        return $this->service->getProductById($product_id, $user_id);
    }

    public function updateProduct(UpdateProductRequest $request)
    {
        return $this->service->updateProduct($request);
    }

    public function deleteProduct($user_id, $product_id)
    {
        return $this->service->deleteProduct($user_id, $product_id);
    }

    public function getAnalytics($user_id)
    {
        return $this->service->getAnalytics($user_id);
    }

    public function addShipping(SellerShippingRequest $request)
    {
        return $this->service->addShipping($request);
    }

    public function getAllShipping($user_id)
    {
        return $this->service->getAllShipping($user_id);
    }

    public function getShippingById($user_id, $shipping_id)
    {
        return $this->service->getShippingById($user_id, $shipping_id);
    }

    public function updateShipping(SellerShippingRequest $request, $shipping_id)
    {
        return $this->service->updateShipping($request, $shipping_id);
    }

    public function deleteShipping($user_id, $shipping_id)
    {
        return $this->service->deleteShipping($user_id, $shipping_id);
    }

    public function setDefault($user_id, $shipping_id)
    {
        return $this->service->setDefault($user_id, $shipping_id);
    }

    public function getComplaints($user_id)
    {
        return $this->service->getComplaints($user_id);
    }

    public function getTemplate()
    {
        return $this->service->getTemplate();
    }


    public function dashboard()
    {
        return $this->service->getDashboardDetails();
    }

    public function getEarningReport()
    {
        return $this->service->getEarningReport();
    }

    public function withdrawalHistory()
    {
        return $this->service->getWithdrawalHistory();
    }

    public function makeWithdrawalRequest(Request $request)
    {
        return $this->service->withdrawalRequest($request);
    }

    //Orders
    public function orderDetails($id)
    {
        return $this->service->getOrderDetails($id);
    }
    //RFQS
    public function allRfq()
    {
        return $this->service->getAllRfq();
    }
    public function rfqDetails($id)
    {
        return $this->service->getRfqDetails($id);
    }
    public function replyReview(Request $request)
    {
        return $this->service->replyRequest($request);
    }
    public function shippOrder(Request $request)
    {
        return $this->service->markShipped($request);
    }
    public function markDelivered(Request $request)
    {
        return $this->service->markDelivered($request);
    }
    public function confirmPayment(Request $request)
    {
        return $this->service->confirmPayment($request);
    }
    public function cancelOrder(Request $request)
    {
        return $this->service->cancelOrder($request);
    }
    public function rateOrder(Request $request)
    {
        return $this->service->rateOrder($request);
    }
    public function orderFeeback(Request $request)
    {
        return $this->service->orderFeeback($request);
    }

    //Seller Wihdrawal method
    public function allWithdrawalMethods()
    {
        return $this->service->getAllMethod();
    }

    public function addWithdrawalMethod(WithdrawalMethodRequest $request)
    {
        return $this->service->addNewMethod($request);
    }

    public function getWithdrawalMethod($id)
    {
        return $this->service->getSingleMethod($id);
    }

    public function makeDefaultAccount(Request $request)
    {
        return $this->service->makeAccounDefaultt($request);
    }

    public function updateWithdrawalMethod(WithdrawalMethodRequest $request, $id)
    {
        return $this->service->updateMethod($request, $id);
    }

    public function deleteWithdrawalMethod($id)
    {
        return $this->service->deleteMethod($id);
    }
}
