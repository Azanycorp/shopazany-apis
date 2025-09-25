<?php

namespace App\Enum;

final class OrderStatus
{
    public const CONFIRMED = 'confirmed';
    public const CANCELLED = 'cancelled';
    public const DELIVERED = 'delivered';
    public const COMPLETED = 'completed';
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const INPROGRESS = 'in-progress';
    public const REVIEW = 'review';
    public const SHIPPED = 'shipped';
    public const PAID = 'paid';
    public const READY_FOR_PICKUP = 'ready_for_pickup';
    public const IN_TRANSIT = 'in_transit';
    public const DISPATCHED = 'dispatched';

    public static function all(): array
    {
        return [
            self::CONFIRMED,
            self::CANCELLED,
            self::DELIVERED,
            self::COMPLETED,
            self::PENDING,
            self::PROCESSING,
            self::INPROGRESS,
            self::REVIEW,
            self::SHIPPED,
            self::PAID,
            self::READY_FOR_PICKUP,
            self::IN_TRANSIT,
            self::DISPATCHED,
        ];
    }
}
