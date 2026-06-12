<?php

namespace App\Filament\Widgets;

use App\Models\DistributorProfile;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingDistributorsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DistributorProfile::query()
                    ->where('is_approved', false)
                    ->with('user')
                    ->latest()
            )
            ->heading('Pending distributor approvals')
            ->description('Review and approve new distributor registrations')
            ->emptyStateHeading('No pending approvals')
            ->emptyStateDescription('All distributor accounts have been reviewed.')
            ->columns([
                TextColumn::make('business_name')
                    ->label('Business')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->copyable(),
                TextColumn::make('user.phone')
                    ->label('Phone')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->since()
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10]);
    }

    public static function canView(): bool
    {
        return DistributorProfile::query()->where('is_approved', false)->exists();
    }
}
