<?php

namespace App\Services;

use App\Models\Order;

class OrderNumberGenerator
{
    public function generate(): string
    {
        $year = now()->year;
        $prefix = "ORD-{$year}-";

        $last = Order::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('order_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
