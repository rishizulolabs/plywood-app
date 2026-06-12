<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'business_name', 'image_path', 'gst_number', 'service_cities',
    'is_approved', 'rating', 'bank_account_name', 'bank_account_number', 'ifsc_code',
])]
class DistributorProfile extends Model
{
    protected function casts(): array
    {
        return [
            'service_cities' => 'array',
            'is_approved' => 'boolean',
            'rating' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
