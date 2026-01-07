<?php

namespace App\Services;

use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Trait\CartTrait;
use App\Trait\HttpResponse;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;

class CartService
{
    use CartTrait, HttpResponse;

    public function __construct(
        private readonly AuthManager $authManager,
        private readonly SessionManager $sessionManager
    ) {}

    public function addToCart($request)
    {
        $currentUserId = userAuth()->id;

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $this->sessionManager->put(['cart_id' => session_id()]);
        $sessionId = $this->sessionManager->get('cart_id');
        $quantity = $request->quantity;

        if ($quantity <= 0) {
            return $this->error(null, 'Quantity should be 1 or more.', 403);
        }

        $product = Product::findOrFail($request->product_id);

        if ($quantity > $product->minimum_order_quantity) {
            return $this->error(null, "You can only order a maximum of {$product->minimum_order_quantity} of this product.", 400);
        }

        $variationId = (int) $request->input('variation_id', 0);

        if ($variationId > 0) {
            $variation = ProductVariation::find($variationId);

            if (! $variation) {
                return $this->error(null, 'Selected variation not found.', 404);
            }

            if ($variation->stock <= 0) {
                return $this->error(null, 'Product is out of stock.', 400);
            }

            if ($quantity > $variation->stock) {
                return $this->error(null, "Only {$variation->stock} units available for this variation.", 400);
            }

            return $this->upsertCart([
                'user_id' => $currentUserId,
                'session_id' => $sessionId,
                'variation_id' => $variation->id,
                'product_id' => $request->product_id,
                'quantity' => $quantity,
                'is_agriecom' => $request->boolean('is_agriecom'),
            ]);
        }

        $product = Product::findOrFail($request->product_id);

        if ($product->current_stock_quantity <= 0) {
            return $this->error(null, 'Product is out of stock.', 400);
        }

        if ($quantity > $product->minimum_order_quantity) {
            return $this->error(null, "You can only order a maximum of {$product->minimum_order_quantity} of this product.", 400);
        }

        if ($quantity > $product->current_stock_quantity) {
            return $this->error(null, "Only {$product->current_stock_quantity} units available.", 400);
        }

        return $this->upsertCart([
            'user_id' => $currentUserId,
            'session_id' => $sessionId,
            'variation_id' => null,
            'product_id' => $request->product_id,
            'quantity' => $quantity,
            'is_agriecom' => $request->boolean('is_agriecom'),
        ]);
    }

    public function getCartItems($userId, Request $request)
    {
        $isAgiecom = $request->boolean('is_agriecom');

        $currentUserId = userAuth()->id;

        if ($currentUserId != $userId) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $sessionId = $this->sessionManager->get('cart_id');

        $cartItems = Cart::with([
            'product.user',
            'product.category',
            'product.subCategory',
            'product.brand',
            'product.shopCountry',
            'variation',
            'variation.product',
            'variation.product.shopCountry',
        ])
            ->where('is_agriecom', $isAgiecom)
            ->when(
                $this->authManager->check(),
                fn ($q) => $q->where('user_id', $userId),
                fn ($q) => $q->where('session_id', $sessionId)
            )
            ->get();

        $localItems = $cartItems->filter(fn ($cartItem): bool => $cartItem->product->getAttribute('country_id') == 160);
        $internationalItems = $cartItems->filter(fn ($cartItem): bool => $cartItem->product->getAttribute('country_id') != 160);
        $defaultCurrency = userAuth()->default_currency;

        $totalLocalPrice = $this->getLocalPrice($localItems, $defaultCurrency);
        $totalInternationalPrice = $this->getInternaltionalPrice($internationalItems, $defaultCurrency);
        $totalDiscount = $this->getTotalDiscount($defaultCurrency);

        return $this->success([
            'local_items' => CartResource::collection($localItems),
            'international_items' => CartResource::collection($internationalItems),
            'total_local_price' => $totalLocalPrice,
            'total_international_price' => $totalInternationalPrice,
            'total_discount_price' => $cartItems->sum($totalDiscount),
            'item_count' => $cartItems->count(),
        ], 'Cart items');
    }

    public function removeCartItem($userId, $cartId)
    {
        $currentUserId = userAuth()->id;

        if ($currentUserId != $userId) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        Cart::where('user_id', $userId)
            ->where('id', $cartId)
            ->delete();

        return $this->success(null, 'Item removed from cart');
    }

    public function clearCart($userId)
    {
        $currentUserId = userAuth()->id;

        if ($currentUserId != $userId) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $sessionId = $this->sessionManager->get('cart_id');

        if ($this->authManager->check()) {
            Cart::where('user_id', $userId)->delete();
        } else {
            Cart::where('session_id', $sessionId)->delete();
        }

        $this->sessionManager->forget('cart_id');

        return $this->success(null, 'Items cleared from cart');
    }

    public function updateCart(Request $request)
    {
        $currentUserId = userAuth()->id;

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $productId = $request->product_id;
        $quantity = $request->quantity;

        if ($quantity <= 0) {
            return $this->error(null, 'Quantity should be 1 or more.', 403);
        }

        $product = Product::findOrFail($request->product_id);

        if ($quantity > $product->minimum_order_quantity) {
            return $this->error(null, 'You have exceeded the minimum order quantity', 400);
        }

        $cartItem = Cart::where('user_id', $currentUserId)
            ->where('product_id', $productId)
            ->firstOrFail();
        $cartItem->update(['quantity' => $quantity]);

        return $this->success(null, 'Cart quantity updated successfully');
    }

    protected function upsertCart(array $data)
    {
        $cart = Cart::updateOrCreate([
            'user_id' => $data['user_id'],
            'session_id' => $data['session_id'],
            'product_id' => $data['product_id'],
        ], [
            'variation_id' => $data['variation_id'],
            'quantity' => $data['quantity'],
            'is_agriecom' => $data['is_agriecom'],
        ]);

        $msg = $cart->wasRecentlyCreated ?
            ['msg' => 'Item added to cart', 'code' => 201] :
            ['msg' => 'Item updated in cart', 'code' => 200];

        return $this->success(null, $msg['msg'], $msg['code']);
    }
}
