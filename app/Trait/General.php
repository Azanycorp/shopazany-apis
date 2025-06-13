<?php

namespace App\Trait;

use App\Enum\OrderStatus;

trait General
{
    use HttpResponse;

    protected function determineOrderStatus($statuses)
    {
        if ($statuses->contains(OrderStatus::PENDING)) {
            return OrderStatus::PENDING;
        } elseif ($statuses->contains(OrderStatus::CONFIRMED)) {
            return OrderStatus::CONFIRMED;
        } elseif ($statuses->contains(OrderStatus::PROCESSING)) {
            return OrderStatus::PROCESSING;
        } elseif ($statuses->contains(OrderStatus::SHIPPED)) {
            return OrderStatus::SHIPPED;
        } elseif ($statuses->contains(OrderStatus::DELIVERED)) {
            return OrderStatus::DELIVERED;
        } elseif ($statuses->contains(OrderStatus::CANCELLED)) {
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
    }
}

