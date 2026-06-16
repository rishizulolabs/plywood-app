<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\DistributorProfile;
use App\Models\Product;
use App\Models\User;

class CustomerCart
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function items(User $user): array
    {
        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with(['product', 'distributorProfile'])
            ->orderBy('created_at')
            ->get();

        return $cartItems->map(function (CartItem $item) use ($user) {
            $product = $item->product;
            $distributor = $item->distributorProfile;

            if (! $distributor && $product) {
                $distributor = InquiryDistributorResolver::forProduct($product, $user);

                if ($distributor) {
                    $item->update(['distributor_profile_id' => $distributor->id]);
                }
            }

            return [
                'product_id' => $item->product_id,
                'product_name' => $product?->name ?? 'Product',
                'quantity' => $item->quantity,
                'min_order_qty' => max(1, (int) ($product?->min_order_qty ?? 1)),
                'distributor_profile_id' => $distributor?->id,
                'distributor' => $distributor?->business_name,
                'notes' => $item->notes,
            ];
        })->all();
    }

    public static function count(User $user): int
    {
        return CartItem::query()->where('user_id', $user->id)->count();
    }

    public static function add(User $user, Product $product, int $quantity, ?DistributorProfile $distributor): void
    {
        $existing = CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $quantity,
                'distributor_profile_id' => $distributor?->id ?? $existing->distributor_profile_id,
            ]);

            return;
        }

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'distributor_profile_id' => $distributor?->id,
            'notes' => null,
        ]);
    }

    public static function remove(User $user, Product $product): void
    {
        CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    public static function update(User $user, Product $product, int $quantity, ?string $notes): void
    {
        CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->update([
                'quantity' => $quantity,
                'notes' => $notes,
            ]);
    }

    /**
     * @param  list<array<string, mixed>>  $sessionItems
     */
    public static function mergeSessionItems(User $user, array $sessionItems): void
    {
        foreach ($sessionItems as $item) {
            $productId = (int) ($item['product_id'] ?? 0);

            if ($productId === 0) {
                continue;
            }

            $product = Product::query()->find($productId);

            if (! $product) {
                continue;
            }

            $distributor = null;

            if (! empty($item['distributor_profile_id'])) {
                $distributor = DistributorProfile::query()->find($item['distributor_profile_id']);
            }

            if (! $distributor) {
                $distributor = InquiryDistributorResolver::forProduct($product, $user);
            }

            self::add($user, $product, (int) ($item['quantity'] ?? 1), $distributor);
        }
    }
}
