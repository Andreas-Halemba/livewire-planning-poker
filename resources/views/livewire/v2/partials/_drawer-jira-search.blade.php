{{-- Jira Such-Interface --}}

{{-- Favoriten-Filter Sektion --}}
<div class="bg-base-300 rounded-lg p-4">
    <div class="flex items-center justify-between mb-3">
        <h4 class="font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-warning" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Deine Jira-Filter
        </h4>
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


