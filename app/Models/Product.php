<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'distributor_profile_id', 'category_id', 'name', 'slug', 'description',
    'thickness', 'size', 'grade', 'core_type', 'number_of_plies', 'brand',
    'is_isi_marked', 'is_standard', 'warranty', 'finish_surface', 'density',
    'termite_borer_treatment', 'weight_per_sheet', 'application', 'glue_type',
    'country_of_origin', 'min_order_qty', 'unit', 'in_stock', 'is_featured',
])]
class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_isi_marked' => 'boolean',
            'termite_borer_treatment' => 'boolean',
            'in_stock' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::random(6);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('thumbnails')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function distributorProfile(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class);
    }

    public function distributors(): BelongsToMany
    {
        return $this->belongsToMany(DistributorProfile::class, 'distributor_product')
            ->withPivot('price', 'stock_quantity')
            ->withTimestamps();
    }

    public function scopeAssignedToDistributor($query, ?int $distributorProfileId = null): void
    {
        $query->whereHas('distributors', function ($distributorQuery) use ($distributorProfileId) {
            $distributorQuery->where('is_approved', true);

            if ($distributorProfileId !== null) {
                $distributorQuery->where('distributor_profiles.id', $distributorProfileId);
            }
        });
    }

    public function isAssignedToDistributor(?int $distributorProfileId = null): bool
    {
        return $this->distributors()
            ->where('is_approved', true)
            ->when($distributorProfileId !== null, fn ($query) => $query->where('distributor_profiles.id', $distributorProfileId))
            ->exists();
    }

    public function inquiryItems(): HasMany
    {
        return $this->hasMany(InquiryItem::class);
    }
}
