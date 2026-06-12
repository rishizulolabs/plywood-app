<x-filament-widgets::widget>
    <div class="stat-cards">
        @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-card-icon stat-card-icon-{{ $stat['color'] }}">
                    <svg class="icon-svg" aria-hidden="true"><use href="#{{ $stat['icon'] }}"></use></svg>
                </div>
                <div class="stat-card-content">
                    <p class="stat-label">{{ $stat['label'] }}</p>
                    <p class="stat-value">{{ $stat['value'] }}</p>
                    <p class="stat-desc">{{ $stat['desc'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
