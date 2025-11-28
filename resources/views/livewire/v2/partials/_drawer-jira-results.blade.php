{{-- Jira Ticket-Auswahl --}}
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
            <label wire:key="jira-ticket-{{ $ticket['key'] }}" class="flex items-start gap-3 p-3 rounded-lg cursor-pointer transition-colors
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


