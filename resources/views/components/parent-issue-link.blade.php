@props([
    'key' => null,
    'title' => null,
    'url' => null,
])

@php
    $key = is_string($key) ? trim($key) : '';
    $title = is_string($title) ? trim($title) : '';
    $url = is_string($url) ? trim($url) : '';
@endphp

@if ($key !== '')
    @if ($url !== '')
        <a href="{{ $url }}" target="_blank" rel="nofollow"
            class="badge badge-xs badge-error badge-outline inline-flex items-center gap-1 hover:bg-error hover:text-error-content"
            title="{{ $title }}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span class="font-medium">{{ $key }}</span>
            @if ($title !== '')
                <span class="truncate max-w-[18ch] hidden sm:inline">· {{ $title }}</span>
            @endif
        </a>
    @else
        <span class="badge badge-xs badge-outline badge-secondary inline-flex items-center gap-1"
            title="{{ $title }}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span class="font-medium">{{ $key }}</span>
            @if ($title !== '')
                <span class="truncate max-w-[18ch] hidden sm:inline">· {{ $title }}</span>
            @endif
        </span>
    @endif
@endif
