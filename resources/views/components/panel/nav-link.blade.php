@props(['href', 'active' => false, 'icon' => 'icon-home'])

<li class="nav-item">
    <a href="{{ $href }}" @class(['nav-link', 'active' => $active])>
        <svg class="nav-icon-svg" aria-hidden="true"><use href="#{{ $icon }}"></use></svg>
        <span>{{ $slot }}</span>
    </a>
</li>
