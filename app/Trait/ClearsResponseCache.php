<?php

namespace App\Trait;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    public static function booted(): void
    {
        self::created(function (): void {
            ResponseCache::clear();
        });

        self::updated(function (): void {
            ResponseCache::clear();
        });

        self::deleted(function (): void {
            ResponseCache::clear();
        });
    }
}
