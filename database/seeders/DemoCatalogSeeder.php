<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Inquiry;
use App\Models\InquiryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $distributor = DistributorProfile::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'distributor@plywood.com'))
            ->first();

        if (! $distributor) {
            return;
        }

        $commercial = Category::query()->where('name', 'Commercial Ply')->first();
        $mdf = Category::query()->where('name', 'MDF')->first();
        $flushDoors = Category::query()->where('name', 'Flush Doors')->first();

        $catalog = [
            [
                'name' => 'Commercial Ply 18mm',
                'category_id' => $commercial?->id,
                'brand' => 'CenturyPly',
                'thickness' => '18mm',
                'size' => '8ft x 4ft',
                'grade' => 'BWR',
                'price' => 5800,
                'stock_quantity' => 50,
            ],
            [
                'name' => 'Bwp',
                'category_id' => $mdf?->id,
                'brand' => 'CenturyPly',
                'thickness' => '12mm',
                'size' => '6ft x 4ft',
                'grade' => 'Commercial Ply',
                'price' => 4200,
                'stock_quantity' => 40,
            ],
            [
                'name' => 'Plybord',
                'category_id' => $flushDoors?->id,
                'brand' => 'CenturyPly',
                'thickness' => '6mm',
                'size' => '7ft x 4ft',
                'grade' => 'BWR',
                'price' => 3900,
                'stock_quantity' => 30,
            ],
        ];

        foreach ($catalog as $item) {
            $product = Product::query()->firstOrCreate(
                ['name' => $item['name']],
                [
                    'distributor_profile_id' => null,
                    'category_id' => $item['category_id'] ?? Category::query()->value('id'),
                    'slug' => Str::slug($item['name']).'-'.Str::lower(Str::random(6)),
                    'brand' => $item['brand'],
                    'thickness' => $item['thickness'],
                    'size' => $item['size'],
                    'grade' => $item['grade'],
                    'in_stock' => true,
                ]
            );

            $distributor->offeredProducts()->syncWithoutDetaching([
                $product->id => [
                    'price' => $item['price'],
                    'stock_quantity' => $item['stock_quantity'],
                ],
            ]);
        }

        Product::query()
            ->whereDoesntHave('distributors', fn ($query) => $query->where('distributor_profiles.id', $distributor->id))
            ->each(function (Product $product) use ($distributor) {
                $distributor->offeredProducts()->syncWithoutDetaching([
                    $product->id => [
                        'price' => 3500,
                        'stock_quantity' => 20,
                    ],
                ]);
            });

        $customer = User::query()->where('email', 'customer@plywood.com')->first();

        if (! $customer || Order::query()->where('customer_id', $customer->id)->exists()) {
            return;
        }

        $product = Product::query()->where('name', 'Commercial Ply 18mm')->first()
            ?? Product::query()->first();

        if (! $product) {
            return;
        }

        $inquiry = Inquiry::create([
            'customer_id' => $customer->id,
            'distributor_profile_id' => $distributor->id,
            'status' => 'converted',
            'delivery_city' => $customer->city ?? 'Delhi',
            'delivery_pincode' => $customer->pincode ?? '110001',
        ]);

        InquiryItem::create([
            'inquiry_id' => $inquiry->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Order::create([
            'inquiry_id' => $inquiry->id,
            'customer_id' => $customer->id,
            'distributor_profile_id' => $distributor->id,
            'total_amount' => 11600,
            'payment_status' => 'pending',
            'fulfillment_status' => 'processing',
            'delivery_address' => trim(($customer->city ?? 'Delhi').', India'),
        ]);
    }
}
