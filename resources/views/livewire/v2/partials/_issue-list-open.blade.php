{{-- Offene Issues Liste --}}
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
                <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <li wire:key="open-issue-{{ $issue->id }}" class="p-4 hover:bg-base-300/50 transition-colors" data-issue-id="{{ $issue->id }}">
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


