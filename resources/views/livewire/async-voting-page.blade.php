<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Header --}}
    <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                Async Voting: <span class="font-bold">{{ $session->name }}</span>
            </h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('session.v2', $session->invite_code) }}" class="btn btn-sm btn-outline">
                    Zur Session (v2)
                </a>
                <a href="{{ route('session.voting', $session->invite_code) }}" class="btn btn-sm btn-ghost">
                    Zur Session (v1)
                </a>
            </div>
        </div>
        <div class="text-sm text-base-content/70 mt-2">
            @if($isOwner)
                Zeigt nur, <span class="font-semibold">wer</span> geschätzt hat (ohne Werte).
            @else
                Wähle ein Issue aus und gib deine Vorab-Schätzung ab.
            @endif
        </div>
    </div>

    @if($isOwner)
        {{-- Owner Progress View --}}
        <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-base sm:text-lg font-semibold">Vorab-Schätzungen (Fortschritt)</h2>
                <div class="text-xs text-base-content/60">
                    {{ $eligibleVoterCount }} Voter
                </div>
            </div>

            @if($openIssues->isEmpty())
                <div class="text-sm text-base-content/60">
                    Keine offenen Issues.
                </div>
            @else
                <div class="divide-y divide-base-300">
                    @foreach($openIssues as $issue)
                        @php
                            $voters = $asyncVotersByIssue[$issue->id] ?? [];
                        @endphp
                        <div class="py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold break-words">
                                        {!! $issue->title_html !!}
                                    </div>
                                    @if($issue->jira_key)
                                        <div class="text-xs text-base-content/60 mt-0.5">{{ $issue->jira_key }}</div>
                                    @endif
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge badge-outline">
                                        {{ count($voters) }}/{{ $eligibleVoterCount }}
                                    </span>
                                </div>
                            </div>

                            @if(!empty($voters))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($voters as $voter)
                                        <span class="badge badge-success badge-outline">
                                            ✓ {{ $voter['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-3 text-xs text-base-content/60">
                                    Noch keine Vorab-Schätzungen.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        {{-- Voter Async Voting View (reuse existing components) --}}
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_380px] gap-6 items-start">
            <div class="min-w-0">
                <livewire:voting-cards :session="$session" key="async-voting-cards-{{ $session->id }}" />
                <livewire:voting.voter :session="$session" :key="'async-voter-'.$session->id" />
            </div>
            <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-5 sm:p-6">
                <div class="text-sm font-semibold mb-2">Hinweis</div>
                <div class="text-sm text-base-content/70 leading-relaxed">
                    Async Votes sind Vorab-Schätzungen und starten keine Live-Runde. Finale Storypoints werden im Session Screen gesetzt.
                </div>
            </div>
        </div>
    @endif
</div>


