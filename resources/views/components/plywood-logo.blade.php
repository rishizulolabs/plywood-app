@props([
    'showText' => true,
    'showIcon' => true,
    'variant' => 'default',
])

@php
    $textClass = match ($variant) {
        'inverse' => 'text-lg font-bold tracking-tight text-white',
        default => 'text-lg font-bold tracking-tight text-amber-950',
    };
@endphp

<a {{ $attributes->merge(['href' => route('home'), 'class' => 'inline-flex items-center gap-2.5 shrink-0']) }}>
    @if($showIcon)
        <svg
            class="h-9 w-9 shrink-0"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <rect x="2" y="4" width="36" height="32" rx="6" fill="#92400E"/>
            <rect x="4" y="8" width="32" height="4" rx="1" fill="#FDE68A" opacity="0.95"/>
            <rect x="4" y="14" width="32" height="4" rx="1" fill="#FCD34D" opacity="0.85"/>
            <rect x="4" y="20" width="32" height="4" rx="1" fill="#FBBF24" opacity="0.75"/>
            <rect x="4" y="26" width="32" height="4" rx="1" fill="#F59E0B" opacity="0.65"/>
            <path d="M8 32h24" stroke="#78350F" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    @endif
    @if($showText)
        <span class="{{ $textClass }}">HATTRICK</span>
    @endif
</a>
