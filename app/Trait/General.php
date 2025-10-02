<?php

namespace App\Trait;

use App\Enum\OrderStatus;

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
}
