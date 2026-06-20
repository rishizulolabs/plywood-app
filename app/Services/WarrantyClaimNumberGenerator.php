<?php

namespace App\Services;

use App\Models\WarrantyClaim;

class WarrantyClaimNumberGenerator
{
    public function generate(): string
    {
        $year = now()->year;
        $prefix = "WC-{$year}-";

        $last = WarrantyClaim::query()
            ->where('claim_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('claim_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
