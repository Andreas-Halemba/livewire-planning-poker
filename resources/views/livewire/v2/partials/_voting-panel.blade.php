{{-- Voting Panel - Aktuelles Issue --}}
<div class="card bg-base-300 text-base-content shadow-lg mb-6 border-2 border-primary">
    <div class="card-body p-5">
        {{-- Header: Status + Controls --}}
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="badge badge-lg badge-warning gap-2">
                @if($votesRevealed)
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{ count($votedUserIds) }} Stimmen
                @else
                    <span class="loading loading-dots loading-xs"></span>
                    Voting läuft · {{ count($votedUserIds) }} Stimmen
                @endif
            </span>

            {{-- Owner Controls --}}
            @if($isOwner)
                <div class="flex gap-2">
                    @if($votesRevealed)
                        <button wire:click="hideVotes" class="btn btn-sm btn-warning gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            Verdecken
                        </button>
                        <button wire:click="restartVoting" class="btn btn-sm btn-secondary gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Neu voten
                        </button>
                    @elseif(count($votedUserIds) > 0)
                        <button wire:click="revealVotes" class="btn btn-sm btn-success gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Aufdecken
                        </button>
                    @endif
                    <button wire:click="cancelVoting" class="btn btn-sm btn-error gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Abbrechen
                    </button>
                </div>
            @endif

            {{-- Voter Controls --}}
            @if(! $isOwner && $myVote !== null && ! $votesRevealed)
                <button wire:click="removeVote" class="btn btn-sm btn-error gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Zurücknehmen
                </button>
            @endif
        </div>

        {{-- Issue Info --}}
        @if($currentIssue->jira_url || $currentIssue->jira_key)
            <a href="{{ $currentIssue->jira_url ?? '#' }}"
               target="_blank"
               class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-1">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z"/>
                </svg>
                {{ $currentIssue->jira_key ?? 'Jira öffnen' }}
            </a>
        @endif
        <h2 class="card-title text-lg">{{ $currentIssue->title }}</h2>

        {{-- Description mit Collapse --}}
        @if($currentIssue->description)
            <div x-data="{ expanded: false }" class="mt-2">
                <div class="relative">
                    <div class="text-base-content text-sm prose prose-sm max-w-none"
                         :class="expanded ? '' : 'max-h-16 overflow-hidden'"
                         x-ref="content">
                        {!! $currentIssue->description !!}
                    </div>
                    <div x-show="!expanded"
                         class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-base-300 to-transparent pointer-events-none">
                    </div>
                </div>
                <button @click="expanded = !expanded"
                        class="btn btn-sm btn-primary mt-2 gap-1">
                    <span x-text="expanded ? 'Weniger anzeigen' : 'Mehr lesen'"></span>
                    <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- Aktionsbereich --}}
        <div class="{{ ($votesRevealed || !$isOwner) ? 'mt-4 pt-4 border-t border-base-content/20' : '' }}">
            @if($votesRevealed)
                {{-- Ergebnis anzeigen --}}
                <div class="text-xs text-base-content mb-2">
                    {{ $isOwner ? 'Schätzung bestätigen:' : 'Ergebnis:' }}
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @php
                        $voteCounts = collect($votesByUser)->values()->countBy()->sortKeys();
                    @endphp
                    @foreach($voteCounts as $vote => $count)
                        @if($isOwner)
                            <button wire:click="confirmEstimate({{ $vote }})"
                                class="btn btn-lg min-w-14 text-lg font-bold btn-success flex-col h-auto py-2">
                                <span>{{ $vote }} SP</span>
                                <span class="text-xs font-normal opacity-80">{{ $count }}x</span>
                            </button>
                        @else
                            <div class="btn btn-lg min-w-14 text-lg font-bold btn-ghost bg-base-content/10 flex-col h-auto py-2 cursor-default no-animation">
                                <span>{{ $vote }} SP</span>
                                <span class="text-xs font-normal opacity-80">{{ $count }}x</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if($isOwner)
                    <div class="text-xs text-base-content mt-3">
                        Klicke auf einen Wert, um die Schätzung zu bestätigen
                    </div>
                @endif
            @else
                {{-- Voting-Karten (für Nicht-Owner) --}}
                @if(! $isOwner)
                    <div x-data="{
                        cards: @js($cards),
                        vote(index) {
                            if (index >= 0 && index < this.cards.length) {
                                $wire.submitVote(this.cards[index]);
                            }
                        }
                    }"
                    @keydown.1.window="vote(0)"
                    @keydown.2.window="vote(1)"
                    @keydown.3.window="vote(2)"
                    @keydown.4.window="vote(3)"
                    @keydown.5.window="vote(4)"
                    @keydown.6.window="vote(5)"
                    @keydown.7.window="vote(6)"
                    @keydown.8.window="vote(7)">
                        <div class="flex items-center gap-2 text-xs text-base-content mb-2">
                            <span>Deine Schätzung:</span>
                            <span class="badge badge-ghost badge-xs">⌨️ Tasten 1-8</span>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @foreach($cards as $index => $card)
                                <button wire:click="submitVote({{ $card }})"
                                    class="btn btn-lg min-w-14 text-lg font-bold relative {{ $myVote == $card ? 'btn-warning' : 'btn-ghost bg-base-content/10 hover:bg-base-content/20' }}">
                                    {{ $card }}
                                    <kbd class="kbd absolute -top-3 -right-3">{{ $index + 1 }}</kbd>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>


