@props([
    /**
     * @var array<int, array{label: string, href?: string|null}>
     */
    'items' => [],
])

@if(!empty($items))
    <nav aria-label="Breadcrumb" class="mb-4">
        <div class="breadcrumbs text-sm">
            <ul class="flex flex-wrap">
                @foreach($items as $idx => $item)
                    @php
                        $isLast = $idx === count($items) - 1;
                        $label = (string) ($item['label'] ?? '');
                        $href = $item['href'] ?? null;
                    @endphp
                    <li class="min-w-0">
                        @if(!$isLast && !empty($href))
                            <a href="{{ $href }}" class="link link-hover inline-flex max-w-[16rem] truncate">
                                {{ $label }}
                            </a>
                        @else
                            <span class="inline-flex max-w-[16rem] truncate text-base-content/70">
                                {{ $label }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>
@endif


