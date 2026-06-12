<?php

namespace App\Services;

use App\Models\Inquiry;

class InquiryNumberGenerator
{
    public function generate(): string
    {
        $year = now()->year;
        $prefix = "INQ-{$year}-";

        $last = Inquiry::where('inquiry_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('inquiry_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
