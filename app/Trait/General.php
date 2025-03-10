<?php

namespace App\Trait;

use App\Enum\OrderStatus;

trait General
{
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
}

