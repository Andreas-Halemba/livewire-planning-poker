<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Session Header --}}
    <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                    Session: <span class="font-bold">{{ $session->name }}</span>
                </h1>
                <div class="text-xs text-warning mt-1">
                    ⚠️ V2 Preview - Schritt 4b: Vote-Werte anzeigen
                </div>
            </div>
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
                            <div class="text-xs text-base-content mb-2">Deine Schätzung:</div>
                            <div class="flex flex-wrap gap-3">
                                @foreach($cards as $card)
                                    <button wire:click="submitVote({{ $card }})"
                                        class="btn btn-lg min-w-14 text-lg font-bold {{ $myVote == $card ? 'btn-warning' : 'btn-ghost bg-base-content/10 hover:bg-base-content/20' }}">
                                        {{ $card }}
                                    </button>
                                @endforeach
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
                        <p class="text-sm">Keine offenen Issues</p>
                    </div>
                @else
                    <ul class="divide-y divide-base-300">
                        @foreach($openIssues as $issue)
                            <li class="p-4 hover:bg-base-300/50 transition-colors">
                                <div class="flex items-center gap-3">
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
                                    {{-- Start-Button nur für Owner --}}
                                    @if($isOwner)
                                        <button wire:click="startVoting({{ $issue->id }})" class="btn btn-primary btn-sm gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Start
                                        </button>
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

</div>
