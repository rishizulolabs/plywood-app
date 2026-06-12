<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        $this->addMediaCollection('images');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function distributorProfile(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class);
    }

    public function inquiryItems(): HasMany
    {
        return $this->hasMany(InquiryItem::class);
    }
}
