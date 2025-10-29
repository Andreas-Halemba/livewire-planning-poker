<div class="w-full shadow-xl card bg-base-300 text-base-content">
    <div class="card-body">
        <h2 class="card-title">{{ __('Create a new session') }}</h2>
        <p>{{ __('Provide a name or topic for your session.') }}</p>
        <form wire:submit="createSession" class="justify-start card-actions">
            @error('sessionName')
                <span class="text-error">{{ $message }}</span>
            @enderror
            <div class="join w-full">
                <input required id="sessionName" wire:model.live="sessionName" type="text" placeholder="Session name"
                    class="join-item input flex-1 @error('sessionName') border-error @enderror" />
                <button class="join-item btn btn-primary" type="submit">{{ __('Create') }}</button>
            </div>
        </form>
    </div>
</div>
