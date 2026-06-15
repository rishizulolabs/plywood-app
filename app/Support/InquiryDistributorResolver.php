<?php

namespace App\Support;

use App\Models\DistributorProfile;
use App\Models\Product;
use App\Models\User;

class InquiryDistributorResolver
{
    public static function forProduct(Product $product, User $customer): ?DistributorProfile
    {
        if ($product->distributor_profile_id) {
            return $product->distributorProfile;
        }

        if ($customer->distributor_profile_id) {
            return DistributorProfile::query()->find($customer->distributor_profile_id);
        }

        $offered = $product->distributors()
            ->where('is_approved', true)
            ->first();

        if ($offered) {
            return $offered;
        }

        return DistributorProfile::query()
            ->where('is_approved', true)
            ->first();
    }
}
