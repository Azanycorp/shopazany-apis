<?php

namespace App\Trait;

use App\Enum\OrderStatus;
use App\Enum\ProductStatus;
use App\Http\Resources\CustomerOrderResource;
use App\Models\Order;
use App\Models\Product;

trait General
{
    use HttpResponse;

    protected function determineOrderStatus($statuses): string
    {
        if ($statuses->contains(OrderStatus::PENDING)) {
            return OrderStatus::PENDING;
        }
        if ($statuses->contains(OrderStatus::CONFIRMED)) {
            return OrderStatus::CONFIRMED;
        }
        if ($statuses->contains(OrderStatus::PROCESSING)) {
            return OrderStatus::PROCESSING;
        }
        if ($statuses->contains(OrderStatus::SHIPPED)) {
            return OrderStatus::SHIPPED;
        }
        if ($statuses->contains(OrderStatus::DELIVERED)) {
            return OrderStatus::DELIVERED;
        }
        if ($statuses->contains(OrderStatus::CANCELLED)) {
            return OrderStatus::CANCELLED;
        }

        return OrderStatus::PENDING;
    }

    protected function handleRewardValidation($status, $data)
    {
        if ($status === 422) {
            return $this->error($data['errors'] ?? null, $data['message'] ?? 'Validation failed', 422);
        }

        if ($status >= 400 && $status < 600) {
            return $this->error(
                $data['errors'] ?? null,
                $data['message'] ?? "Request failed with status code $status",
                $status
            );
        }

        return null;
    }

    protected function searchByProduct($countryId, string $search, ?int $categoryId = null)
    {
        $products = Product::where('status', ProductStatus::ACTIVE)
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->when($search, fn ($q) => $q->whereAny(['name', 'description'], 'like', "%{$search}%"))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->select('id', 'name', 'slug', 'price', 'image', 'category_id', 'discount_price', 'default_currency')
            ->withCount('productReviews as total_reviews')
            ->withAvg('productReviews as average_rating', 'rating')
            ->get()
            ->map(fn ($product) => tap($product, function ($p) {
                $p->average_rating = $p->average_rating ? round($p->average_rating, 1) : 0;
            }));

        return $this->success($products, 'Product search results');
    }

    protected function searchByOrder(string $search, $userId)
    {
        $orders = Order::withRelationShips()
            ->where('user_id', $userId)
            ->when($search, fn ($q) => $q->whereLike('order_no', "%{$search}%"))
            ->paginate(25);

        $data = CustomerOrderResource::collection($orders);

        return $this->withPagination($data, 'Order search results');
    }

    protected function applyPromoTransaction($user, $promo, $products, $promoRedeemAction, $cartService)
    {
        $cartResponse = $cartService->getCartItems($user->id);

        if ($cartResponse->getStatusCode() !== 200) {
            return $cartResponse;
        }

        /** @var array $cart */
        $cart = $cartResponse->getData(true);

        if (blank($cart['data'])) {
            return $this->error(null, 'Cart is empty.', 400);
        }

        $originalAmount = $this->getCartTotal($cart);
        $currency = 'USD';

        foreach ($products as $product) {
            $currency = $product->shopCountry->currency ?? $product->productVariations->product->shopCountry->currency;
        }

        if ($promo->discount_type === 'percent') {
            $discount = round(($promo->discount / 100) * $originalAmount);
        } else {
            $discount = currencyConvert(
                $currency,
                $promo->discount,
                $user->default_currency
            );
        }

        $discountAmount = min($discount, $originalAmount);
        $totalAmount = max(0, $originalAmount - $discountAmount);

        foreach ($products as $product) {
            $promoRedeemAction->handle(
                $user->id,
                $promo->id,
                $product->id
            );
        }

        return $this->success([
            'original_amount' => round($originalAmount, 2),
            'discounted_amount' => round($discountAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ], 'Promo code applied successfully.');
    }

    protected function getCartTotal(array $cart): float
    {
        return
            ($cart['data']['total_local_price'] ?? 0)
            + ($cart['data']['total_international_price'] ?? 0)
            - ($cart['data']['total_discount_price'] ?? 0);
    }
}
