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

    protected function searchByProduct(int $countryId, string $search, ?int $categoryId = null)
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

    protected function searchByOrder(string $search, int $userId)
    {
        $orders = Order::withRelationShips()
            ->where('user_id', $userId)
            ->when($search, fn ($q) => $q->whereLike('order_no', "%{$search}%"))
            ->paginate(25);

        $data = CustomerOrderResource::collection($orders);

        return $this->withPagination($data, 'Order search results');
    }
}
