{{-- Participant Card Component --}}
<div class="flex items-center gap-3 p-2.5 rounded-lg border-2 transition-all {{ $cardBg }} {{ $borderColor }}">
    {{-- Avatar mit Online-Dot --}}
    <div class="relative">
        <div class="w-9 h-9 rounded-full text-sm font-semibold flex items-center justify-center flex-shrink-0 {{ $avatarBg }} {{ $avatarText }}">
            @if($icon === 'PO')
                <span class="text-xs font-bold">PO</span>
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
        <span class="text-[10px] uppercase tracking-wider text-error font-semibold">{{ $badge }}</span>
    @endif
</div>

