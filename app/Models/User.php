<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'phone',
    'company_name', 'gst_number', 'address', 'city', 'state', 'pincode',
    'distributor_profile_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }

    public function distributorProfile(): HasOne
    {
        return $this->hasOne(DistributorProfile::class);
    }

    public function assignedDistributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_profile_id');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'customer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
