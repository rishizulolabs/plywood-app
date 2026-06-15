<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function offeredProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'distributor_product')
            ->withPivot('price', 'stock_quantity')
            ->withTimestamps();
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDetailPayload(): array
    {
        return [
            'name' => $this->user?->name ?? $this->business_name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'location' => $this->user?->city ?? ($this->service_cities[0] ?? null),
            'service_cities' => $this->service_cities ?? [],
            'status' => $this->is_approved ? 'Approved' : 'Not approved',
            'is_approved' => $this->is_approved,
            'registered' => $this->created_at?->format('d M Y'),
            'gst_number' => $this->gst_number,
            'rating' => $this->rating,
            'bank_account_name' => $this->bank_account_name,
            'bank_account_number' => $this->bank_account_number,
            'ifsc_code' => $this->ifsc_code,
            'total_stock_quantity' => (int) $this->offeredProducts->sum(fn ($product) => (int) ($product->pivot->stock_quantity ?? 0)),
            'allotted_products_count' => $this->offeredProducts->count(),
            'offered_products' => $this->offeredProducts->map(fn ($product) => [
                'name' => $product->name,
                'grade' => $product->grade,
                'size' => $product->size,
                'category' => $product->category?->name,
                'price' => format_inr($product->pivot->price),
                'stock_quantity' => (int) ($product->pivot->stock_quantity ?? 0),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, float>
     */
    public function offeredProductPriceMap(): array
    {
        return $this->offeredProducts
            ->mapWithKeys(fn ($product) => [(string) $product->id => (float) $product->pivot->price])
            ->all();
    }

    public function restockRequests(): HasMany
    {
        return $this->hasMany(RestockRequest::class);
    }
}
