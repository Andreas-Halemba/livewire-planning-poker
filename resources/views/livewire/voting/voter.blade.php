<div>
    @php
        $allUpcomingIssues = $session->issues->where('status', '!=', 'finished')->where('status', '!=', 'voting');
        $estimatedIssues = $session->issues->where('status', 'finished');

        // Separate issues: those without votes vs those with async votes
        $upcomingIssues = $allUpcomingIssues->filter(function ($issue) {
            return !\App\Models\Vote::whereUserId(auth()->id())->whereIssueId($issue->id)->exists();
        });

        $asyncEstimatedIssues = $allUpcomingIssues->filter(function ($issue) {
            return \App\Models\Vote::whereUserId(auth()->id())->whereIssueId($issue->id)->exists();
        });
    @endphp

    <!-- Upcoming Issues (Collapsible) -->
    @if($upcomingIssues->count() > 0)
        <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-4" x-data="{ open: false }">
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between text-base font-semibold text-base-content cursor-pointer hover:text-primary transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Noch zu schätzen
                    <span class="text-base-content/70 font-normal text-sm">({{ $upcomingIssues->count() }} Issues)</span>
                </div>
                <svg
                    class="w-5 h-5 text-base-content/70 transition-transform"
                    :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="open"
                x-collapse
                class="mt-4 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-2">
                    @foreach($upcomingIssues as $issue)
                        <div wire:click.prevent="$dispatch('select-issue', { issueId: {{ $issue->id }} })"
                            class="flex items-center justify-between p-3 bg-base-200 rounded-lg border border-base-300 cursor-pointer hover:bg-base-300 hover:border-accent transition-colors group">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-semibold text-base-content/70">
                                    {{ $issue->jira_key ?? 'Issue' }}
                                </div>
                                <div class="text-sm sm:text-base text-base-content mt-1 truncate">
                                    {{ $issue->title }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 ml-2 flex-shrink-0">
                                @if($loop->first)
                                    <span class="text-xs font-semibold px-2 py-1 bg-warning/20 text-warning rounded uppercase">
                                        Nächstes
                                    </span>
                                @else
                                    <span class="text-xs font-semibold px-2 py-1 bg-base-300 text-base-content/70 rounded uppercase">
                                        Wartend
                                    </span>
                                @endif
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/40 group-hover:text-accent transition-colors"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Async Estimated Issues (Collapsible) -->
    @if($asyncEstimatedIssues->count() > 0)
        <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-4" x-data="{ open: false }">
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between text-base font-semibold text-base-content cursor-pointer hover:text-primary transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Asynchron geschätzt
                    <span class="text-base-content/70 font-normal text-sm">({{ $asyncEstimatedIssues->count() }} Issues)</span>
                </div>
                <svg
                    class="w-5 h-5 text-base-content/70 transition-transform"
                    :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="open"
                x-collapse
                class="mt-4 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-2">
                    @foreach($asyncEstimatedIssues as $issue)
                        @php
                            $userVote = \App\Models\Vote::whereUserId(auth()->id())->whereIssueId($issue->id)->first();
                            $voteValue = $userVote && $userVote->value !== null ? $userVote->value : ($userVote ? '?' : null);
                        @endphp
                        <div wire:click.prevent="$dispatch('select-issue', { issueId: {{ $issue->id }} })"
                            class="flex items-center justify-between p-3 bg-success/10 rounded-lg border border-success/30 cursor-pointer hover:bg-success/20 hover:border-success/50 transition-colors group">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="text-xs sm:text-sm font-semibold text-base-content/70">
                                        {{ $issue->jira_key ?? 'Issue' }}
                                    </div>
                                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-success text-success-content rounded-lg text-xs sm:text-sm font-bold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>{{ $voteValue === '?' ? '?' : $voteValue . ' SP' }}</span>
                                    </div>
                                </div>
                                <div class="text-sm sm:text-base text-base-content mt-1 truncate">
                                    {{ $issue->title }}
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/40 group-hover:text-accent transition-colors"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Estimated Issues History (Collapsible) -->
    @if($estimatedIssues->count() > 0)
        <div class="bg-base-100 rounded-xl shadow-sm p-5 sm:p-6" x-data="{ open: false }">
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between text-base font-semibold text-base-content cursor-pointer hover:text-primary transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Bereits geschätzt
                    <span class="text-base-content/70 font-normal text-sm">({{ $estimatedIssues->count() }} Issues)</span>
                </div>
                <svg
                    class="w-5 h-5 text-base-content/70 transition-transform"
                    :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="open"
                x-collapse
                class="mt-4 pt-4 border-t border-base-300">
                <div class="flex flex-col gap-2">
                    @foreach($estimatedIssues as $issue)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg border border-base-300">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-semibold text-base-content/70">
                                    {{ $issue->jira_key ?? 'Issue' }}
                                </div>
                                <div class="text-sm sm:text-base text-base-content mt-1 truncate">
                                    {{ $issue->title }}
                                </div>
                            </div>
                            <div class="text-base sm:text-lg font-semibold text-success ml-2 flex-shrink-0">
                                {{ $issue->storypoints }} SP
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
