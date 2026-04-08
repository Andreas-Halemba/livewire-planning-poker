{{-- Participant Card Component --}}
<div class="flex items-center gap-3 p-2.5 rounded-lg border-2 transition-all {{ $cardBg }} {{ $borderColor }}">
    {{-- Avatar mit Online-Dot --}}
    <div class="relative">
        <div class="w-9 h-9 rounded-full text-sm font-semibold flex items-center justify-center flex-shrink-0 {{ $avatarBg }} {{ $avatarText }}">
            @if($icon === 'PO')
                <span class="text-xs font-bold">PO</span>
            @elseif($icon === 'EYE')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            @elseif($icon === '✓')
                <span class="text-lg">✓</span>
            @elseif($icon === '?')
                <span class="text-base">?</span>
            @else
                {{-- Vote-Wert (Zahl) --}}
                <span class="text-sm font-bold">{{ $icon }}</span>
            @endif
        </div>
        {{-- Online/Offline Dot --}}
        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-base-100 {{ $dotColor }}"></span>
    </div>

    {{-- Name --}}
    <div class="flex-1 min-w-0">
        <div class="text-sm font-medium break-words {{ $isOnline ? 'text-base-content' : 'text-base-content/60' }}">
            {{ $user->name }}
            @if($isCurrentUser)
                <span class="text-xs text-base-content/50">(Du)</span>
            @endif
        </div>
    </div>

    {{-- Badge (optional) --}}
    @if($badge)
        <span @class([
            'text-[10px] uppercase tracking-wider font-semibold',
            'text-error' => ($badgeTone ?? 'error') === 'error',
            'text-info' => ($badgeTone ?? 'error') === 'info',
        ])>{{ $badge }}</span>
    @endif
</div>

