<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsWidget;
use App\Filament\Widgets\AdminWelcomeWidget;
use App\Filament\Widgets\PendingDistributorsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\WidgetConfiguration;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Platform Overview';

    protected static ?int $navigationSort = -2;

    /**
     * @return array<class-string<\Filament\Widgets\Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            AdminWelcomeWidget::class,
            AdminStatsWidget::class,
            PendingDistributorsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 1;
    }
}
