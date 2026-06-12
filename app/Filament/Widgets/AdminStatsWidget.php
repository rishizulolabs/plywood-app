<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\Widget;

class AdminStatsWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-stats';

    protected function getViewData(): array
    {
        $inquiriesThisMonth = Inquiry::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $ordersThisMonth = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $gmvThisMonth = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $pendingDistributors = DistributorProfile::query()
            ->where('is_approved', false)
            ->count();

        return [
            'stats' => [
                [
                    'label' => 'Customers',
                    'value' => User::role('customer')->count(),
                    'desc' => 'Registered buyers',
                    'color' => 'blue',
                    'icon' => 'icon-users',
                ],
                [
                    'label' => 'Distributors',
                    'value' => User::role('distributor')->count(),
                    'desc' => $pendingDistributors.' pending approval',
                    'color' => $pendingDistributors > 0 ? 'amber' : 'green',
                    'icon' => 'icon-users',
                ],
                [
                    'label' => 'Products',
                    'value' => Product::count(),
                    'desc' => Category::count().' categories',
                    'color' => 'purple',
                    'icon' => 'icon-database',
                ],
                [
                    'label' => 'Inquiries (month)',
                    'value' => $inquiriesThisMonth,
                    'desc' => 'Quote requests this month',
                    'color' => 'amber',
                    'icon' => 'icon-file-text',
                ],
                [
                    'label' => 'Orders (month)',
                    'value' => $ordersThisMonth,
                    'desc' => 'Confirmed orders this month',
                    'color' => 'green',
                    'icon' => 'icon-check-circle',
                ],
                [
                    'label' => 'GMV (month)',
                    'value' => format_inr($gmvThisMonth),
                    'desc' => 'Gross merchandise value',
                    'color' => 'green',
                    'icon' => 'icon-dollar-sign',
                ],
            ],
        ];
    }
}
