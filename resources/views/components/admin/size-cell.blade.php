@props(['size' => null])

@php
    $display = filled($size) ? trim((string) $size) : '—';
@endphp

{{ $display }}
