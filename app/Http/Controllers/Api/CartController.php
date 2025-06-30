<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $service
    ) {}

    public function addToCart(CartRequest $request)
    {
        return $this->service->addToCart($request);
    }

    public function getCartItems($userId)
    {
        return $this->service->getCartItems($userId);
    }

    public function removeCartItem($userId, $cartId)
    {
        return $this->service->removeCartItem($userId, $cartId);
    }

    public function clearCart($userId)
    {
        return $this->service->clearCart($userId);
    }

    public function updateCart(Request $request)
    {
        return $this->service->updateCart($request);
    }
}
