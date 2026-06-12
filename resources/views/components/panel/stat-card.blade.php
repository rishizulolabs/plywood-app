@props(['label', 'value', 'color' => 'blue'])

@php
$colors = [
    'blue' => 'stat-card-icon-blue',
    'green' => 'stat-card-icon-green',
    'amber' => 'stat-card-icon-amber',
    'purple' => 'stat-card-icon-purple',
    'primary' => 'stat-card-icon-blue',
];
$iconClass = $colors[$color] ?? 'stat-card-icon-blue';
@endphp

<div {{ $attributes->class(['stat-card']) }}>
    <div class="stat-card-icon {{ $iconClass }}">
        {{ $iconSlot ?? '' }}
    </div>
    <div class="stat-card-content">
        <p class="stat-label">{{ $label }}</p>
        <p class="stat-value">{{ $value }}</p>
        @isset($description)
            <p class="stat-description">{{ $description }}</p>
        @endisset
    </div>
</div>
