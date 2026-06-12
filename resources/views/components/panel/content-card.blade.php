@props(['title' => null])

<div {{ $attributes->class(['content-card']) }}>
    @if($title || isset($header))
        <div class="content-card-header">
            @if($title)
                <h2 class="content-card-title">{{ $title }}</h2>
            @endif
            {{ $header ?? '' }}
        </div>
    @endif
    <div style="padding: 1.5rem;">
        {{ $slot }}
    </div>
</div>
