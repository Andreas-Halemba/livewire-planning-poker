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


