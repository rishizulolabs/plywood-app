<?php

namespace Database\Seeders;

use App\Models\DistributorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::updateOrCreate(
            ['email' => 'customer@plywood.com'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('admin@123'),
                'email_verified_at' => now(),
                'company_name' => 'Sharma Interiors',
                'city' => 'Delhi',
            ]
        );
        $customer->syncRoles(['customer']);

        $distributorUser = User::updateOrCreate(
            ['email' => 'distributor@plywood.com'],
            [
                'name' => 'Demo Distributor',
                'password' => Hash::make('admin@123'),
                'email_verified_at' => now(),
                'phone' => '9876543210',
            ]
        );
        $distributorUser->syncRoles(['distributor']);

        $distributorProfile = DistributorProfile::updateOrCreate(
            ['user_id' => $distributorUser->id],
            [
                'business_name' => 'Kumar Plywood Distributors',
                'gst_number' => '07AABCU9603R1ZM',
                'service_cities' => ['Delhi', 'Noida'],
                'is_approved' => true,
            ]
        );

        $customer->update([
            'distributor_profile_id' => $distributorProfile->id,
        ]);
    }
}
