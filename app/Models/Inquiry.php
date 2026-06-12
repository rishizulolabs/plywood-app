<?php

namespace App\Models;

use App\Services\InquiryNumberGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'inquiry_number', 'customer_id', 'distributor_profile_id', 'status',
    'customer_notes', 'delivery_city', 'delivery_pincode', 'expected_by',
])]
class Inquiry extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'expected_by' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Inquiry $inquiry) {
            if (empty($inquiry->inquiry_number)) {
                $inquiry->inquiry_number = app(InquiryNumberGenerator::class)->generate();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status'])->logOnlyDirty();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function distributorProfile(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InquiryItem::class);
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
