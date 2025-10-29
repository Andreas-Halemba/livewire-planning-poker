<div>
    @if(auth()->user() && auth()->user()->jira_url && auth()->user()->jira_user && auth()->user()->jira_api_key)
        <div class="collapse collapse-arrow bg-base-200">
            <input type="checkbox" />
            <div class="collapse-title text-base font-semibold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Import from Jira
            </div>
            <div class="collapse-content">
                <form wire:submit="loadTickets" class="flex flex-col gap-3 mt-2">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-base-content/70">Project Key</label>
                        <x-text-input required class="input-sm" wire:model="projectKey" placeholder="e.g. SAN" />
                        @error('projectKey')
                            <span class="text-xs text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-base-content/70">Status</label>
                        <select class="select select-sm w-full" wire:model="status" required>
                            <option value="">Select Status</option>
                            @foreach($this->statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="text-xs text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm mt-2" wire:loading.attr="disabled"
                        wire:target="loadTickets">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="loadTickets">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Load Tickets
                        </span>
                        <span wire:loading wire:target="loadTickets" class="loading loading-spinner loading-sm"></span>
                    </button>
                </form>

                @if(isset($message) && $message)
                    <div class="mt-3 alert alert-sm alert-{{ $messageType === 'error' ? 'error' : ($messageType === 'success' ? 'success' : ($messageType === 'warning' ? 'warning' : 'info')) }}">
                        <span class="text-xs">{{ $message }}</span>
                    </div>
                @endif
            </div>
        </div>
    @else
    <div class="flex flex-col gap-2">
        <div class="alert alert-warning text-warning-content">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-5 w-5 shrink-0 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1 flex flex-col gap-2">
                <span class="text-sm">Konfiguriere Jira-Zugangsdaten, um Tickets zu importieren</span>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn  btn-accent w-full">
            Jetzt konfigurieren
        </a>
    </div>
    @endif

    <!-- Ticket Selection Modal -->
    @if($showModal && !empty($availableTickets))
        <div class="modal modal-open" wire:click="closeModal">
            <div class="modal-box max-w-4xl relative" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Select Tickets to Import
                    </h3>
                    <button class="btn btn-sm btn-circle btn-ghost" wire:click="closeModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if($message && ($messageType === 'warning' || $messageType === 'error'))
                    <div class="mb-4 alert alert-{{ $messageType }} alert-sm">
                        <span class="text-xs">{{ $message }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between mb-4 p-3 bg-base-200 rounded-lg">
                    <label class="cursor-pointer label flex items-center gap-2">
                        <input type="checkbox" class="checkbox checkbox-primary checkbox-sm" wire:click="toggleSelectAll"
                            @if(count($selectedTickets) === count($availableTickets)) checked @endif />
                        <span class="text-sm font-medium">Select All</span>
                    </label>
                    <span class="badge badge-sm badge-primary">
                        {{ count($selectedTickets) }} / {{ count($availableTickets) }} selected
                    </span>
                </div>

                <div class="overflow-y-auto max-h-96 space-y-2" wire:key="tickets-list">
                    @foreach($availableTickets as $ticket)
                        <div class="card card-sm bg-base-200 {{ $ticket['already_imported'] ? 'border border-base-300' : '' }}">
                            <div class="card-body">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" class="mt-1 checkbox checkbox-primary checkbox-sm {{ $ticket['already_imported'] ? 'opacity-50' : '' }}"
                                        value="{{ $ticket['key'] }}"
                                        wire:key="ticket-{{ $ticket['key'] }}"
                                        wire:model.live="selectedTickets"
                                        @if($ticket['already_imported']) disabled @endif />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-sm {{ $ticket['already_imported'] ? 'text-base-content/60' : 'text-base-content' }}">{{ $ticket['key'] }}</span>
                                            @if($ticket['already_imported'])
                                                <span class="badge badge-warning badge-sm">Already Imported</span>
                                            @endif
                                        </div>
                                        <p class="text-sm {{ $ticket['already_imported'] ? 'text-base-content/60' : 'text-base-content font-medium' }}">{{ $ticket['title'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="modal-action mt-4">
                    <button class="btn btn-primary btn-sm" wire:click="importSelectedTickets" wire:loading.attr="disabled"
                        @if(empty($selectedTickets)) disabled @endif>
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="importSelectedTickets">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Import Selected ({{ count($selectedTickets) }})
                        </span>
                        <span wire:loading wire:target="importSelectedTickets" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
