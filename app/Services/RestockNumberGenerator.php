<?php

namespace App\Services;

use App\Models\RestockRequest;

class RestockNumberGenerator
{
    public function generate(): string
    {
        $year = now()->year;
        $prefix = "RST-{$year}-";

        $last = RestockRequest::where('request_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('request_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
