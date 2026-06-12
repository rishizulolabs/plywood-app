@props(['title', 'icon' => 'icon-home'])

<div class="nav-section">
    <div class="nav-section-title">
        <svg class="icon-svg" aria-hidden="true"><use href="#{{ $icon }}"></use></svg>
        <span>{{ $title }}</span>
    </div>
    <ul class="nav-list">
        {{ $slot }}
    </ul>
</div>
