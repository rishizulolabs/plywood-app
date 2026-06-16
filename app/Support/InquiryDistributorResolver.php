<?php

namespace App\Support;

use App\Models\DistributorProfile;
use App\Models\Product;
use App\Models\User;

class InquiryDistributorResolver
{
    public static function forProduct(Product $product, User $customer): ?DistributorProfile
    {
        if ($customer->distributor_profile_id) {
            $assigned = DistributorProfile::query()
                ->whereKey($customer->distributor_profile_id)
                ->where('is_approved', true)
                ->first();

            if ($assigned && $product->isAssignedToDistributor($assigned->id)) {
                return $assigned;
            }
        }

        if ($product->distributor_profile_id) {
            return $product->distributorProfile;
        }

        return $product->distributors()
            ->where('is_approved', true)
            ->first();
    }
}
