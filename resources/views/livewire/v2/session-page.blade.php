@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endassets

<div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- V2 Preview Alert --}}
    <div role="alert" class="alert alert-info alert-outline alert-vertical sm:alert-horizontal mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current h-6 w-6 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <h3 class="font-bold">V2 Preview</h3>
            <div class="text-xs">Diese Version ist noch in Entwicklung. Bei Problemen zur stabilen V1 wechseln.</div>
        </div>
        <a href="{{ route('session.voting', $session->invite_code) }}" class="btn btn-sm btn-info btn-outline">
            Zur V1
        </a>
    </div>

    {{-- Session Header --}}
    <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                Session: <span class="font-bold">{{ $session->name }}</span>
            </h1>
            <div class="text-sm text-base-content/70">
                <span class="text-success font-medium">{{ $onlineCount }} online</span> •
                {{ $participantCount }} Mitglieder •
                {{ $finishedCount }}/{{ $issueCount }} Issues
            </div>
        </div>

        {{-- Teilnehmer-Liste --}}
        <div class="text-sm font-semibold mt-4 mb-3 uppercase tracking-wide">Teilnehmer</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
            @foreach($session->users as $user)
                @php
                    $isCurrentUser = $user->id === Auth::id();
                    $userIsOwner = $user->id === $session->owner_id;
                    $isOnline = in_array($user->id, $onlineUserIds);
                    $hasVoted = in_array($user->id, $votedUserIds);
                    $userVote = $votesByUser[$user->id] ?? null;
                    $votingActive = $currentIssue !== null;

                    // Voting-Status bestimmen (nur für Nicht-Owner)
                    if ($userIsOwner) {
                        $avatarBg = 'bg-accent';
                        $avatarText = 'text-accent-content';
                        $borderColor = 'border-accent';
                        $cardBg = 'bg-accent/5';
                        $icon = 'PO';
                        $badge = null;
                    } elseif (! $votingActive) {
                        $avatarBg = 'bg-base-300';
                        $avatarText = 'text-base-content';
                        $borderColor = 'border-base-300';
                        $cardBg = 'bg-base-100';
                        $icon = '?';
                        $badge = null;
                    } elseif ($votesRevealed && ! $hasVoted) {
                        $avatarBg = 'bg-error';
                        $avatarText = 'text-error-content';
                        $borderColor = 'border-error';
                        $cardBg = 'bg-error/5';
                        $icon = '?';
                        $badge = 'SKIPPED';
                    } elseif ($votesRevealed && $hasVoted) {
                        // Revealed: Zeige den Vote-Wert
                        $avatarBg = 'bg-success';
                        $avatarText = 'text-success-content';
                        $borderColor = 'border-success';
                        $cardBg = 'bg-success/5';
                        $icon = $userVote; // Zeigt "5", "8" etc.
                        $badge = null;
                    } elseif ($hasVoted) {
                        // Voted aber noch nicht revealed
                        $avatarBg = 'bg-success';
                        $avatarText = 'text-success-content';
                        $borderColor = 'border-success';
                        $cardBg = 'bg-success/5';
                        $icon = '✓';
                        $badge = null;
                    } else {
                        $avatarBg = 'bg-warning';
                        $avatarText = 'text-warning-content';
                        $borderColor = 'border-warning';
                        $cardBg = 'bg-warning/5';
                        $icon = '?';
                        $badge = null;
                    }

                    $dotColor = $isOnline ? 'bg-success' : 'bg-base-content/30';
                @endphp

                <div
                    class="flex items-center gap-3 p-2.5 rounded-lg border-2 transition-all {{ $cardBg }} {{ $borderColor }}">
                    <div class="relative">
                        <div
                            class="w-9 h-9 rounded-full text-sm font-semibold flex items-center justify-center flex-shrink-0 {{ $avatarBg }} {{ $avatarText }}">
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
                        <span
                            class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-base-100 {{ $dotColor }}"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div
                            class="text-sm font-medium break-words {{ $isOnline ? 'text-base-content' : 'text-base-content/60' }}">
                            {{ $user->name }}
                            @if($isCurrentUser)
                                <span class="text-xs text-base-content/50">(Du)</span>
                            @endif
                        </div>
                    </div>
                    @if($badge)
                        <span class="text-[10px] uppercase tracking-wider text-error font-semibold">{{ $badge }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Aktuelles Voting (wenn aktiv) --}}
    @if($currentIssue)
        <div class="card bg-base-300 text-base-content shadow-lg mb-6 border-2 border-primary">
            <div class="card-body p-5">
                {{-- Header: Status + Owner Controls --}}
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

                    {{-- Owner Controls (immer rechts oben) --}}
                    @if($isOwner)
                        <div class="flex gap-2">
                            {{-- Aufdecken / Verdecken --}}
                            @if($votesRevealed)
                                <button wire:click="hideVotes" class="btn btn-sm btn-warning gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                    Verdecken
                                </button>
                                {{-- Neu voten --}}
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
                            {{-- Abbrechen --}}
                            <button wire:click="cancelVoting" class="btn btn-sm btn-error gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Abbrechen
                            </button>
                        </div>
                    @endif

                    {{-- Voter Controls (nur wenn gevoted und nicht aufgedeckt) --}}
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
                @if($currentIssue->description)
                    <div x-data="{ expanded: false }" class="mt-2">
                        <div class="relative">
                            <div class="text-base-content text-sm prose prose-sm max-w-none"
                                 :class="expanded ? '' : 'max-h-16 overflow-hidden'"
                                 x-ref="content">
                                {!! $currentIssue->description !!}
                            </div>
                            {{-- Fade overlay --}}
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
                        {{-- Ergebnis anzeigen (für alle) --}}
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
    @endif

    {{-- Issue Listen --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        {{-- Offene Issues --}}
        <div class="card bg-base-200 shadow-lg border border-base-300">
            <div class="card-body p-4 pb-0">
                <h2 class="card-title text-base">
                    <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Offen
                    <span class="badge badge-warning badge-sm">{{ $openIssues->count() }}</span>
                </h2>
            </div>
            <div class="p-0">
                @if($openIssues->isEmpty())
                    <div class="p-8 text-center text-base-content/50">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm mb-3">Keine offenen Issues</p>
                        @if($isOwner)
                            <button wire:click="$set('drawerOpen', true)" class="btn btn-primary btn-sm gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Issue hinzufügen
                            </button>
                        @endif
                    </div>
                @else
                    <ul class="divide-y divide-base-300"
                        @if($isOwner)
                            x-data
                            x-init="
                                Sortable.create($el, {
                                    animation: 150,
                                    handle: '.drag-handle',
                                    ghostClass: 'opacity-50',
                                    onEnd: function(evt) {
                                        const items = [...evt.to.children].map(el => parseInt(el.dataset.issueId));
                                        $wire.updateIssueOrder(items);
                                    }
                                });
                            "
                        @endif
                    >
                        @foreach($openIssues as $issue)
                            <li class="p-4 hover:bg-base-300/50 transition-colors" data-issue-id="{{ $issue->id }}">
                                <div class="flex items-center gap-3">
                                    {{-- Drag Handle (nur für Owner) --}}
                                    @if($isOwner)
                                        <div class="drag-handle cursor-grab active:cursor-grabbing text-base-content/40 hover:text-base-content">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        @if($issue->jira_url || $issue->jira_key)
                                            <a href="{{ $issue->jira_url ?? '#' }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-0.5">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z"/>
                                                </svg>
                                                {{ $issue->jira_key }}
                                            </a>
                                        @endif
                                        <p class="font-medium text-base-content truncate">
                                            {{ $issue->title }}
                                        </p>
                                    </div>
                                    {{-- Buttons nur für Owner --}}
                                    @if($isOwner)
                                        <div class="flex items-center gap-1">
                                            <button wire:click="startVoting({{ $issue->id }})" class="btn btn-primary btn-sm gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Start
                                            </button>
                                            <button wire:click="deleteIssue({{ $issue->id }})"
                                                    wire:confirm="Issue '{{ $issue->title }}' wirklich löschen?"
                                                    class="btn btn-ghost btn-sm btn-square text-error hover:bg-error hover:text-error-content">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Geschätzte Issues --}}
        <div class="card bg-base-200 shadow-lg border border-base-300">
            <div class="card-body p-4 pb-0">
                <h2 class="card-title text-base">
                    <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Geschätzt
                    <span class="badge badge-success badge-sm">{{ $finishedIssues->count() }}</span>
                </h2>
            </div>
            <div class="p-0">
                @if($finishedIssues->isEmpty())
                    <div class="p-8 text-center text-base-content/50">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm">Noch keine Issues geschätzt</p>
                    </div>
                @else
                    <ul class="divide-y divide-base-300">
                        @foreach($finishedIssues as $issue)
                            <li class="p-4 hover:bg-base-300/50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        @if($issue->jira_url || $issue->jira_key)
                                            <a href="{{ $issue->jira_url ?? '#' }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-0.5">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z"/>
                                                </svg>
                                                {{ $issue->jira_key }}
                                            </a>
                                        @endif
                                        <p class="font-medium text-base-content truncate">
                                            {{ $issue->title }}
                                        </p>
                                    </div>
                                    {{-- Story Points Badge --}}
                                    <span class="badge badge-success">
                                        {{ $issue->storypoints ?? '?' }} SP
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>

    {{-- FAB für Owner: Issue hinzufügen --}}
    @if($isOwner)
        <div class="fixed bottom-6 right-6 z-50">
            <label for="add-issue-drawer" class="btn btn-primary btn-circle btn-lg shadow-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </label>
        </div>
    @endif

</div>

{{-- Drawer für Issue hinzufügen (außerhalb des Haupt-Divs für korrektes Overlay) --}}
@if($isOwner)
    <div class="drawer drawer-end z-50"
         x-data
         @keydown.escape.window="$wire.set('drawerOpen', false)">
        <input id="add-issue-drawer" type="checkbox" class="drawer-toggle" wire:model.live="drawerOpen" />
        <div class="drawer-side">
            <label for="add-issue-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="bg-base-100 min-h-full w-[80vw] max-w-4xl p-6 shadow-xl">
                {{-- Drawer Header --}}
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold">Issue hinzufügen</h3>
                    <label for="add-issue-drawer" class="btn btn-sm btn-ghost btn-circle">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </label>
                </div>

                {{-- Tabs --}}
                <div class="grid grid-cols-2 gap-2 mb-6">
                    <button wire:click="$set('drawerTab', 'manual')"
                            class="btn {{ $drawerTab === 'manual' ? 'btn-primary' : 'btn-ghost border border-base-300' }} gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Manuell
                    </button>
                    <button wire:click="$set('drawerTab', 'jira')"
                            class="btn {{ $drawerTab === 'jira' ? 'btn-primary' : 'btn-ghost border border-base-300' }} gap-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z"/>
                        </svg>
                        Jira
                    </button>
                </div>

                {{-- Manual Tab --}}
                @if($drawerTab === 'manual')
                    <form wire:submit="addIssue">
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Titel *</span>
                            </label>
                            <input type="text"
                                   wire:model="newIssueTitle"
                                   placeholder="Issue-Titel eingeben..."
                                   class="input input-bordered w-full"
                                   required />
                            @error('newIssueTitle')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Beschreibung</span>
                            </label>
                            <textarea wire:model="newIssueDescription"
                                      placeholder="Optionale Beschreibung..."
                                      class="textarea textarea-bordered w-full h-24"
                            ></textarea>
                        </div>

                        <div class="divider text-xs">Jira (optional)</div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Issue Key</span>
                            </label>
                            <input type="text"
                                   wire:model="newIssueJiraKey"
                                   placeholder="z.B. SAN-1234"
                                   class="input input-bordered w-full" />
                        </div>

                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-medium">Jira URL</span>
                            </label>
                            <input type="url"
                                   wire:model="newIssueJiraUrl"
                                   placeholder="https://jira.example.com/browse/SAN-1234"
                                   class="input input-bordered w-full" />
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Issue hinzufügen
                        </button>
                    </form>
                @endif

                {{-- Jira Tab --}}
                @if($drawerTab === 'jira')
                    @if(!$this->hasJiraCredentials())
                        {{-- Keine Jira-Credentials --}}
                        <div class="alert alert-warning mb-4">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <h4 class="font-semibold">Jira nicht konfiguriert</h4>
                                <p class="text-sm">Konfiguriere deine Jira-Zugangsdaten in den Profileinstellungen.</p>
                            </div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary w-full">
                            Jetzt konfigurieren
                        </a>
                    @else
                        {{-- Jira Import UI --}}
                        <div class="space-y-6">

                            {{-- Error/Success Messages --}}
                            @if($jiraError)
                                <div class="alert alert-error text-sm">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $jiraError }}</span>
                                </div>
                            @endif

                            @if($jiraSuccess)
                                <div class="alert alert-success text-sm">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $jiraSuccess }}</span>
                                </div>
                            @endif

                            {{-- Wenn noch keine Tickets geladen --}}
                            @if(empty($jiraTickets))

                                {{-- Favoriten-Filter Sektion --}}
                                <div class="bg-base-300 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-semibold flex items-center gap-2">
                                            <svg class="w-5 h-5 text-warning" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            Deine Jira-Filter
                                        </h4>
                                        {{-- Refresh Button --}}
                                        <button wire:click="refreshJiraFilters"
                                                wire:loading.attr="disabled"
                                                wire:target="loadJiraFilters, refreshJiraFilters"
                                                class="btn btn-xs btn-ghost btn-circle"
                                                title="Filter neu laden">
                                            <svg wire:loading.remove wire:target="loadJiraFilters, refreshJiraFilters" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span wire:loading wire:target="loadJiraFilters, refreshJiraFilters" class="loading loading-spinner loading-xs"></span>
                                        </button>
                                    </div>

                                    {{-- Loading State --}}
                                    @if($jiraLoading && !$jiraFiltersLoaded)
                                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                                            <span class="loading loading-spinner loading-sm"></span>
                                            Filter werden geladen...
                                        </div>
                                    @elseif($jiraFiltersLoaded)
                                        @if(empty($jiraFilters))
                                            <p class="text-sm text-base-content/60">
                                                Keine Favoriten-Filter gefunden. Markiere Filter in Jira als Favorit.
                                            </p>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($jiraFilters as $filter)
                                                    <button wire:click="loadFromFilter('{{ $filter['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            class="btn btn-sm btn-ghost justify-start w-full text-left gap-2 hover:bg-base-100">
                                                        <svg class="w-4 h-4 text-info shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                        </svg>
                                                        <span class="truncate">{{ $filter['name'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-sm text-base-content/60">
                                            Filter werden automatisch geladen...
                                        </p>
                                    @endif
                                </div>

                                <div class="divider text-xs text-base-content/50">oder</div>

                                {{-- URL/Keys Input --}}
                                <div>
                                    <label class="label">
                                        <span class="label-text font-semibold flex items-center gap-2">
                                            <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                            URL oder Issue-Keys
                                        </span>
                                    </label>
                                    <textarea wire:model.live="jiraInput"
                                              placeholder="Füge hier ein:&#10;• Jira URL mit JQL (z.B. .../issues?jql=...)&#10;• Filter-URL (z.B. .../issues/?filter=12345)&#10;• Issue-Keys (SAN-123, SAN-456, SAN-789)"
                                              class="textarea textarea-bordered w-full h-28 text-sm font-mono"
                                    ></textarea>
                                    <div class="text-xs text-base-content/60 mt-2 space-y-1">
                                        <p class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Kopiere einfach die URL aus deinem Jira-Browser
                                        </p>
                                    </div>
                                </div>

                                <button wire:click="loadFromInput"
                                        wire:loading.attr="disabled"
                                        class="btn btn-primary w-full gap-2"
                                        @if(empty(trim($jiraInput))) disabled @endif>
                                    <svg wire:loading.remove wire:target="loadFromInput" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <span wire:loading.remove wire:target="loadFromInput">Tickets laden</span>
                                    <span wire:loading wire:target="loadFromInput" class="loading loading-spinner"></span>
                                </button>

                            @else
                                {{-- Ticket-Liste zur Auswahl --}}
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-semibold">
                                            {{ count($jiraTickets) }} Ticket(s) gefunden
                                        </h4>
                                        <button wire:click="resetJiraImport" class="btn btn-sm btn-ghost">
                                            ← Zurück
                                        </button>
                                    </div>

                                    {{-- Select All / Deselect --}}
                                    <div class="flex items-center justify-between mb-3 p-3 bg-base-300 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            @if(count($selectedJiraTickets) === count(array_filter($jiraTickets, fn($t) => !$t['alreadyImported'])))
                                                <button wire:click="deselectAllJiraTickets" class="btn btn-xs btn-ghost">
                                                    Auswahl aufheben
                                                </button>
                                            @else
                                                <button wire:click="selectAllJiraTickets" class="btn btn-xs btn-ghost">
                                                    Alle auswählen
                                                </button>
                                            @endif
                                        </div>
                                        <span class="badge badge-primary">
                                            {{ count($selectedJiraTickets) }} / {{ count(array_filter($jiraTickets, fn($t) => !$t['alreadyImported'])) }} ausgewählt
                                        </span>
                                    </div>

                                    {{-- Ticket List --}}
                                    <div class="max-h-80 overflow-y-auto">
                                        <div class="space-y-2 p-1">
                                        @foreach($jiraTickets as $index => $ticket)
                                            <label class="flex items-start gap-3 p-3 rounded-lg cursor-pointer transition-colors
                                                {{ $ticket['alreadyImported'] ? 'bg-base-200 opacity-60' : 'bg-base-300 hover:bg-base-100' }}
                                                {{ in_array($ticket['key'], $selectedJiraTickets) ? 'ring-2 ring-primary' : 'ring-1 ring-transparent' }}">
                                                <input type="checkbox"
                                                       wire:model.live="selectedJiraTickets"
                                                       value="{{ $ticket['key'] }}"
                                                       class="checkbox checkbox-primary checkbox-sm mt-0.5"
                                                       @if($ticket['alreadyImported']) disabled @endif />
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="font-mono text-sm font-semibold text-info">{{ $ticket['key'] }}</span>
                                                        @if($ticket['alreadyImported'])
                                                            <span class="badge badge-warning badge-xs">bereits importiert</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm text-base-content truncate">{{ $ticket['title'] }}</p>
                                                </div>
                                            </label>
                                        @endforeach
                                        </div>
                                    </div>

                                    {{-- Import Button --}}
                                    <button wire:click="importSelectedJiraTickets"
                                            wire:loading.attr="disabled"
                                            class="btn btn-success w-full mt-4 gap-2"
                                            @if(empty($selectedJiraTickets)) disabled @endif>
                                        <svg wire:loading.remove wire:target="importSelectedJiraTickets" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span wire:loading.remove wire:target="importSelectedJiraTickets">{{ count($selectedJiraTickets) }} Ticket(s) importieren</span>
                                        <span wire:loading wire:target="importSelectedJiraTickets" class="loading loading-spinner"></span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif
</div>
