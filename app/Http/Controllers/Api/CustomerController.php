<?php

namespace App\Http\Controllers\Api;

use App\Actions\CalculateOrderSummaryAction;
use App\Actions\PromoRedeemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddShippingRequest;
use App\Http\Requests\CustomerSupportRequest;
use App\Http\Requests\OrderRateRequest;
use App\Http\Requests\PurchaseServiceRequest;
use App\Http\Requests\RedeemPointRequest;
use App\Http\Requests\RedeemPromoRequest;
use App\Services\User\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $service
    ) {}

    public function dashboardAnalytics(int $userId)
    {
        return $this->service->dashboardAnalytics($userId);
    }

    public function acountOverview(int $userId)
    {
        return $this->service->acountOverview($userId);
    }

    public function recentOrders(int $userId)
    {
        return $this->service->recentOrders($userId);
    }

    public function getOrders(int $userId, Request $request)
    {
        return $this->service->getOrders($userId, $request);
    }

    public function getOrderDetail($orderNo, CalculateOrderSummaryAction $summary)
    {
        return $this->service->getOrderDetail($orderNo, $summary);
    }

    public function rateOrder(OrderRateRequest $request)
    {
        return $this->service->rateOrder($request);
    }

    public function support(CustomerSupportRequest $request)
    {
        return $this->service->support($request);
    }

    public function wishlist(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer'],
        ]);

        return $this->service->wishlist($request);
    }

    public function getWishlist(int $userId)
    {
        return $this->service->getWishlist($userId);
    }

    public function getSingleWishlist(int $userId, $id)
    {
        return $this->service->getSingleWishlist($userId, $id);
    }

    public function removeWishlist(int $userId, $id)
    {
        return $this->service->removeWishlist($userId, $id);
    }

    public function rewardDashboard(int $userId)
    {
        return $this->service->rewardDashboard($userId);
    }

    public function activity(int $userId)
    {
        return $this->service->activity($userId);
    }

    public function redeemPoint(RedeemPointRequest $request)
    {
        return $this->service->redeemPoint($request);
    }

    public function getCategories()
    {
        return $this->service->getCategories();
    }

    public function getServicesByCategory($slug)
    {
        return $this->service->getServicesByCategory($slug);
    }

    public function getServices(Request $request)
    {
        return $this->service->getServices($request);
    }

    public function getCompanies()
    {
        return $this->service->getCompanies();
    }

    public function getCompanyDetail($slug)
    {
        return $this->service->getCompanyDetail($slug);
    }

    public function purchaseService(PurchaseServiceRequest $request)
    {
        return $this->service->purchaseService($request);
    }

    public function getCustomers()
    {
        return $this->service->getCustomers();
    }

    public function shipping(AddShippingRequest $request)
    {
        return $this->service->shipping($request);
    }

    public function redeemPromo(RedeemPromoRequest $request, PromoRedeemAction $promoRedeemAction)
    {
        return $this->service->redeemPromo($request, $promoRedeemAction);
    }
}
