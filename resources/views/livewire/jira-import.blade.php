<div class="box-border shadow-xl card bg-base-100 card-compact">
    <div class="justify-between card-body">
        <form wire:submit="importTickets">
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
                    wire:target="importTickets">
                    <span wire:loading.remove wire:target="importTickets">Import from Jira</span>
                    <span wire:loading wire:target="importTickets" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>

        @if($message)
            <div
                class="mt-3 alert alert-{{ $messageType === 'error' ? 'error' : ($messageType === 'success' ? 'success' : ($messageType === 'warning' ? 'warning' : 'info')) }}">
                <span>{{ $message }}</span>
            </div>
        @endif
    </div>
</div>
