<?php

namespace App\Models;

use App\Services\RestockNumberGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'request_number', 'distributor_profile_id', 'product_id',
    'quantity', 'unit_price', 'total_amount', 'status',
])]
class RestockRequest extends Model
{
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RestockRequest $request) {
            if (empty($request->request_number)) {
                $request->request_number = app(RestockNumberGenerator::class)->generate();
            }
        });
    }

    public function distributorProfile(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
