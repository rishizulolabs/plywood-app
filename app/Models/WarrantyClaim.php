<?php

namespace App\Models;

use App\Services\WarrantyClaimNumberGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'claim_number',
    'user_id',
    'order_id',
    'order_number',
    'product_name',
    'complaint',
    'status',
    'admin_notes',
])]
class WarrantyClaim extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'complaint' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WarrantyClaim $claim) {
            if (empty($claim->claim_number)) {
                $claim->claim_number = app(WarrantyClaimNumberGenerator::class)->generate();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('claim_photos');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
