<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\Product;
use App\Models\User;

class ApiController extends Controller
{
    protected function jsonSuccess(array $data = [], ?string $message = null, int $status = 200): \Illuminate\Http\JsonResponse
    {
        $payload = $data;

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    protected function jsonError(string $message, int $status = 400, array $errors = []): \Illuminate\Http\JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * @return array<string, mixed>
     */
    protected function userPayload(User $user): array
    {
        $role = $user->getRoleNames()->first();

        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'company_name' => $user->company_name,
            'gst_number' => $user->gst_number,
            'address' => $user->address,
            'city' => $user->city,
            'state' => $user->state,
            'pincode' => $user->pincode,
            'role' => $role,
        ];

        if ($role === 'distributor' && $user->distributorProfile) {
            $profile = $user->distributorProfile;
            $payload['distributor_profile'] = [
                'id' => $profile->id,
                'business_name' => $profile->business_name,
                'is_approved' => $profile->is_approved,
                'gst_number' => $profile->gst_number,
                'service_cities' => $profile->service_cities ?? [],
            ];
        }

        if ($role === 'customer' && $user->assignedDistributor) {
            $distributor = $user->assignedDistributor;
            $payload['assigned_distributor'] = [
                'id' => $distributor->id,
                'business_name' => $distributor->business_name,
                'is_approved' => $distributor->is_approved,
            ];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function productPayload(Product $product, ?int $distributorProfileId = null, ?User $user = null): array
    {
        $offered = $this->resolveProductOffer($product, $distributorProfileId, $user);

        $price = (float) ($offered?->pivot->price ?? 0);
        $stockQuantity = (int) ($offered?->pivot->stock_quantity ?? 0);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'thickness' => $product->thickness,
            'size' => $product->size,
            'grade' => $product->grade,
            'brand' => $product->brand,
            'unit' => $product->unit,
            'min_order_qty' => max(1, (int) ($product->min_order_qty ?? 1)),
            'in_stock' => (bool) $product->in_stock,
            'is_featured' => (bool) $product->is_featured,
            'image_url' => $this->productImageUrl($product),
            'thumbnail_urls' => $product->getMedia('thumbnails')
                ->map(fn ($media) => $this->absoluteUrl($media->getUrl()))
                ->filter()
                ->values()
                ->all(),
            'core_type' => $product->core_type,
            'number_of_plies' => $product->number_of_plies,
            'is_standard' => $product->is_standard,
            'warranty' => $product->warranty,
            'finish_surface' => $product->finish_surface,
            'glue_type' => $product->glue_type,
            'density' => $product->density,
            'weight_per_sheet' => $product->weight_per_sheet,
            'termite_borer_treatment' => (bool) $product->termite_borer_treatment,
            'application' => $product->application,
            'country_of_origin' => $product->country_of_origin,
            'price' => $price,
            'price_formatted' => format_inr($price),
            'stock_quantity' => $stockQuantity,
        ];
    }

    protected function resolveProductOffer(
        Product $product,
        ?int $preferredDistributorId = null,
        ?User $user = null,
    ): ?\Illuminate\Database\Eloquent\Model {
        if ($preferredDistributorId) {
            $offered = $product->distributors()
                ->where('distributor_profiles.id', $preferredDistributorId)
                ->where('is_approved', true)
                ->first();

            if ($offered) {
                return $offered;
            }
        }

        if ($user?->distributor_profile_id) {
            $offered = $product->distributors()
                ->where('distributor_profiles.id', $user->distributor_profile_id)
                ->where('is_approved', true)
                ->first();

            if ($offered) {
                return $offered;
            }
        }

        return $product->distributors()
            ->where('is_approved', true)
            ->orderByPivot('price')
            ->first();
    }

    protected function priceForProduct(
        Product $product,
        ?int $distributorProfileId = null,
        ?User $user = null,
    ): float {
        return (float) ($this->resolveProductOffer($product, $distributorProfileId, $user)?->pivot->price ?? 0);
    }

    protected function customerDistributor(User $user): ?DistributorProfile
    {
        $distributor = $user->assignedDistributor;

        if (! $distributor?->is_approved) {
            return null;
        }

        return $distributor;
    }

    protected function productImageUrl(Product $product): ?string
    {
        $url = $product->getFirstMediaUrl('product_image')
            ?: $product->getFirstMediaUrl('thumbnails');

        return $this->absoluteUrl($url ?: null);
    }

    protected function absoluteUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        $base = rtrim(request()->getSchemeAndHttpHost(), '/');

        return $base.'/'.ltrim($url, '/');
    }
}
