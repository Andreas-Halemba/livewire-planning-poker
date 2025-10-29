<div>
    @if(auth()->user() && auth()->user()->jira_url && auth()->user()->jira_user && auth()->user()->jira_api_key)
        <div class="box-border shadow-xl card bg-base-100 card-compact">
            <div class="justify-between card-body">
                <form wire:submit="loadTickets">
                    <div class="card-title">Import from Jira</div>
                    <div class="gap-3 mt-3 form-control">
                        <x-text-input required class="input-md" wire:model="projectKey" placeholder="Project Key (z.B. SAN)" />
                        @error('projectKey')
                            <span class="text-error">{{ $message }}</span>
                        @enderror

                        <select class="select select-bordered w-full" wire:model="status" required>
                            <option value="">Select Status</option>
                            @foreach($this->statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="text-error">{{ $message }}</span>
                        @enderror

                        <button type="submit" class="btn btn-primary btn-sm btn-outline" wire:loading.attr="disabled"
                            wire:target="loadTickets">
                            <span wire:loading.remove wire:target="loadTickets">Load Tickets</span>
                            <span wire:loading wire:target="loadTickets" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>

                @if(isset($message) && $message)
                    <div
                        class="mt-3 alert alert-{{ $messageType === 'error' ? 'error' : ($messageType === 'success' ? 'success' : ($messageType === 'warning' ? 'warning' : 'info')) }}">
                        <span>{{ $message }}</span>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="box-border shadow-xl card bg-base-100 card-compact">
            <div class="card-body">
                <div class="card-title">Import from Jira</div>
                <div class="p-4 rounded-lg bg-base-200 border border-base-300">
                    <p class="text-sm text-base-content">
                        {{ __('Please configure your Jira credentials in your profile settings to import issues from Jira.') }}
                    </p>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm mt-3">
                        {{ __('Go to Profile Settings') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Ticket Selection Modal -->
    @if($showModal && !empty($availableTickets))
        <div class="modal modal-open" wire:click="closeModal">
            <div class="modal-box max-w-4xl relative" wire:click.stop>
                <h3 class="text-lg font-bold mb-4">Select Tickets to Import</h3>
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeModal">✕</button>

                @if($message && ($messageType === 'warning' || $messageType === 'error'))
                    <div class="mt-3 alert alert-{{ $messageType }}">
                        <span>{{ $message }}</span>
                    </div>
                @endif

                <div class="mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="form-control">
                            <label class="cursor-pointer label">
                                <input type="checkbox" class="checkbox checkbox-primary" wire:click="toggleSelectAll"
                                    @if(count($selectedTickets) === count($availableTickets)) checked @endif />
                                <span class="ml-2 label-text">Select All</span>
                            </label>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ count($selectedTickets) }} / {{ count($availableTickets) }} selected
                        </span>
                    </div>

                    <div class="overflow-y-auto max-h-96">
                        <div class="space-y-2" wire:key="tickets-list">
                            @foreach($availableTickets as $ticket)
                                <div
                                    class="p-3 border rounded-lg {{ $ticket['already_imported'] ? 'bg-gray-100 opacity-75' : '' }}">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" class="mt-1 checkbox checkbox-primary"
                                            value="{{ $ticket['key'] }}"
                                            wire:key="ticket-{{ $ticket['key'] }}"
                                            wire:model.live="selectedTickets"
                                            @if($ticket['already_imported']) disabled @endif />
                                        <label class="flex-1 cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold">{{ $ticket['key'] }}</span>
                                                @if($ticket['already_imported'])
                                                    <span class="badge badge-warning badge-sm">Already Imported</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-gray-700">{{ $ticket['title'] }}</p>
                                            @if($ticket['description'])
                                                <p class="mt-1 text-xs text-gray-500 line-clamp-2">
                                                    {{ Str::limit($ticket['description'], 100) }}</p>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            <div class="modal-action">
                <button class="btn btn-primary" wire:click="importSelectedTickets" wire:loading.attr="disabled"
                    @if(empty($selectedTickets)) disabled @endif>
                    <span wire:loading.remove wire:target="importSelectedTickets">Import Selected</span>
                    <span wire:loading wire:target="importSelectedTickets"
                        class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
