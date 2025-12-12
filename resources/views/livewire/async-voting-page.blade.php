<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Session: ' . $session->name, 'href' => route('session.voting', $session->invite_code)],
        ['label' => 'Async Voting'],
    ]" />

    {{-- Session Header (v2-like) --}}
    <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                    Session: <span class="font-bold">{{ $session->name }}</span>
                </h1>
                <div class="mt-1 text-xs sm:text-sm text-base-content/70">
                    @if($isOwner)
                        Zeigt nur, <span class="font-semibold">wer</span> geschätzt hat – keine Werte.
                    @else
                        Vorab-Schätzungen ohne Live-Runde. Finale Storypoints setzt der Owner im Session Screen.
                    @endif
                </div>
            </div>
            <div class="flex flex-col sm:items-end gap-2">
                <div class="text-sm text-base-content/70">
                    {{ $openIssues->count() }} offene Issues • {{ $eligibleVoterCount }} Voter
                </div>
                <a href="{{ route('session.voting', $session->invite_code) }}" class="btn btn-sm btn-info btn-outline">
                    Zur Session (Voting)
                </a>
            </div>
        </div>
    </div>

    @if($isOwner)
        {{-- Owner Progress View --}}
        <div class="card bg-base-200 shadow-lg border border-base-300">
            <div class="card-body p-5 sm:p-6">
                <h2 class="card-title text-base sm:text-lg">
                    <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Vorab-Schätzungen (Fortschritt)
                </h2>

                @if($openIssues->isEmpty())
                    <div class="text-sm text-base-content/60 mt-2">
                        Keine offenen Issues.
                    </div>
                @else
                    <div class="divide-y divide-base-300 mt-3">
                        @foreach($openIssues as $issue)
                            @php
                                $voters = $asyncVotersByIssue[$issue->id] ?? [];
                                $votersShown = array_slice($voters, 0, 10);
                                $remaining = max(count($voters) - count($votersShown), 0);
                            @endphp
                            <div class="py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        @if($issue->jira_url || $issue->jira_key)
                                            <a href="{{ $issue->jira_url ?? '#' }}" target="_blank" rel="nofollow"
                                                class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-0.5">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z" />
                                                </svg>
                                                {{ $issue->jira_key ?? 'Jira öffnen' }}
                                            </a>
                                        @endif
                                        <div class="text-sm font-semibold break-words">
                                            {{ $issue->title }}
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge badge-outline">
                                            {{ count($voters) }}/{{ $eligibleVoterCount }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center flex-wrap gap-2">
                                    @if(!empty($voters))
                                        <div class="flex -space-x-2">
                                            @foreach($votersShown as $voter)
                                                @php
                                                    $name = (string) $voter['name'];
                                                    $parts = preg_split('/\s+/', trim($name)) ?: [];
                                                    $initials = collect($parts)->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                                                    if ($initials === '' && $name !== '') {
                                                        $initials = mb_strtoupper(mb_substr($name, 0, 2));
                                                    }
                                                @endphp
                                                <div class="tooltip" data-tip="{{ $name }}">
                                                    <div class="avatar avatar-placeholder">
                                                        <div class="bg-success/15 text-success rounded-full w-8 h-8 ring-2 ring-success/30 flex items-center justify-center">
                                                            <span class="text-[11px] font-semibold leading-none">{{ $initials }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($remaining > 0)
                                                <div class="avatar avatar-placeholder">
                                                    <div class="bg-base-300 text-base-content rounded-full w-8 h-8 ring-2 ring-base-300 flex items-center justify-center">
                                                        <span class="text-[11px] font-semibold leading-none">+{{ $remaining }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-xs text-base-content/60">
                                            haben geschätzt
                                        </div>
                                    @else
                                        <div class="text-xs text-base-content/60">
                                            Noch keine Vorab-Schätzungen.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Voter Async Voting View (v2-style) --}}
        <div class="min-w-0 space-y-6">
            <livewire:async-voting-cards :session="$session" :key="'async-v2-cards-'.$session->id" />

            {{-- Not yet voted --}}
            <div class="card bg-base-200 shadow-lg border border-base-300">
                <div class="card-body p-4 pb-0">
                    <h2 class="card-title text-base">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Noch zu schätzen
                        <span class="badge badge-warning badge-sm">{{ $notVotedIssues->count() }}</span>
                    </h2>
                </div>
                <div class="p-0">
                    @if($notVotedIssues->isEmpty())
                        <div class="p-6 text-center text-base-content/50 text-sm">
                            Alles erledigt 🎉
                        </div>
                    @else
                        <ul class="divide-y divide-base-300">
                            @foreach($notVotedIssues as $issue)
                                <li wire:key="async-notvoted-{{ $issue->id }}"
                                    class="p-4 hover:bg-base-300/50 transition-colors cursor-pointer"
                                    x-data
                                    @click="$dispatch('async-select-issue', { issueId: {{ $issue->id }} })">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            @if($issue->jira_url || $issue->jira_key)
                                                <a href="{{ $issue->getJiraBrowserUrl() }}"
                                                    target="_blank"
                                                    rel="nofollow"
                                                    class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-0.5"
                                                    @click.stop>
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                        <path
                                                            d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z" />
                                                    </svg>
                                                    {{ $issue->jira_key ?? 'Jira öffnen' }}
                                                </a>
                                            @endif
                                            <div class="flex items-center gap-2">
                                                <x-issue-type-badge :type="$issue->issue_type" />
                                            </div>
                                            <div class="font-medium text-base-content truncate">{{ $issue->title }}</div>
                                        </div>
                                        <span class="badge badge-accent">schätzen</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Already voted --}}
            <div class="card bg-base-200 shadow-lg border border-base-300">
                <div class="card-body p-4 pb-0">
                    <h2 class="card-title text-base">
                        <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Vorab geschätzt
                        <span class="badge badge-success badge-sm">{{ $votedIssues->count() }}</span>
                    </h2>
                </div>
                <div class="p-0">
                    @if($votedIssues->isEmpty())
                        <div class="p-6 text-center text-base-content/50 text-sm">
                            Noch keine Vorab-Schätzungen gespeichert.
                        </div>
                    @else
                        <ul class="divide-y divide-base-300">
                            @foreach($votedIssues as $issue)
                                @php
                                    $voteVal = $myVotesByIssue[$issue->id] ?? null;
                                @endphp
                                <li wire:key="async-voted-{{ $issue->id }}"
                                    class="p-4 hover:bg-base-300/50 transition-colors cursor-pointer"
                                    x-data
                                    @click="$dispatch('async-select-issue', { issueId: {{ $issue->id }} })">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            @if($issue->jira_url || $issue->jira_key)
                                                <a href="{{ $issue->getJiraBrowserUrl() }}"
                                                    target="_blank"
                                                    rel="nofollow"
                                                    class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-0.5"
                                                    @click.stop>
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                        <path
                                                            d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z" />
                                                    </svg>
                                                    {{ $issue->jira_key ?? 'Jira öffnen' }}
                                                </a>
                                            @endif
                                            <div class="flex items-center gap-2">
                                                <x-issue-type-badge :type="$issue->issue_type" />
                                            </div>
                                            <div class="font-medium text-base-content truncate">{{ $issue->title }}</div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="badge badge-success badge-outline whitespace-nowrap shrink-0">
                                                {{ $voteVal }} {{ ($issue->estimate_unit ?? 'sp') === 'hours' ? 'h' : 'SP' }}
                                            </span>
                                            <button
                                                type="button"
                                                class="btn btn-ghost btn-sm btn-square text-error tooltip tooltip-left"
                                                wire:click.prevent="revokeAsyncVote({{ $issue->id }})"
                                                wire:confirm="Vorab-Schätzung widerrufen? (Das Ticket bleibt erhalten.)"
                                                @click.stop
                                                aria-label="Vorab-Schätzung widerrufen"
                                                data-tip="Vorab-Schätzung widerrufen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
