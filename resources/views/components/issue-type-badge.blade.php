@props([
    'type' => null,
])

@php
    $raw = is_string($type) ? trim($type) : '';
    $label = $raw !== '' ? $raw : null;
    $normalized = $label ? strtolower($label) : null;

    // Styling convention:
    // - Spike: warning (hours estimation)
    // - Bug: error (later: non-estimable / special handling)
    // - Everything else: outline
    $badge = match ($normalized) {
        'epic' => 'badge-primary',
        'spike' => 'badge-warning',
        'user-story' => 'badge-success',
        'bug' => 'badge-error',
        default => 'badge-info',
    };
@endphp

@if($label)
    <span class="badge badge-xs {{ $badge }}">{{ $label }}</span>
@endif
