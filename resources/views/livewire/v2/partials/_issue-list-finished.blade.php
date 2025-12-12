{{-- Geschätzte Issues Liste --}}
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
                <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">Noch keine Issues geschätzt</p>
            </div>
        @else
            <ul class="divide-y divide-base-300">
                @foreach($finishedIssues as $issue)
                    <li wire:key="finished-issue-{{ $issue->id }}" class="p-4 hover:bg-base-300/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    @if($issue->jira_url || $issue->jira_key)
                                        <a href="{{ $issue->jira_url ?? '#' }}" target="_blank" rel="nofollow"
                                            class="inline-flex items-center gap-1 text-xs text-info hover:underline">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z" />
                                            </svg>
                                            {{ $issue->jira_key }}
                                        </a>
                                    @endif
                                    <x-issue-type-badge :type="$issue->issue_type" />
                                </div>
                                <p class="font-medium text-base-content truncate">
                                    {{ $issue->title }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-success">
                                    {{ $issue->storypoints ?? '?' }} {{ ($issue->estimate_unit ?? 'sp') === 'hours' ? 'h' : 'SP' }}
                                </span>
                                @if($isOwner && $issue->jira_key)
                                    <button
                                        wire:click="refreshIssueFromJira({{ $issue->id }})"
                                        class="btn btn-ghost btn-sm btn-square tooltip tooltip-left"
                                        data-tip="Jira-Daten aktualisieren"
                                        wire:loading.attr="disabled"
                                        wire:target="refreshIssueFromJira">
                                        <svg wire:loading.remove wire:target="refreshIssueFromJira" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v6h6M20 20v-6h-6M20 8a8 8 0 00-14.66-3.34M4 16a8 8 0 0014.66 3.34" />
                                        </svg>
                                        <span wire:loading wire:target="refreshIssueFromJira" class="loading loading-spinner loading-xs"></span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
