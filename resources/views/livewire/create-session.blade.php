<div class="w-full shadow-xl card bg-base-300 text-base-content">
    <div class="card-body">
        <h2 class="card-title">{{ __('Create a new session') }}</h2>
        <p>{{ __('Provide a name or topic for your session.') }}</p>
        <form
            wire:submit.prevent="createSession"
            class="justify-start card-actions"
        >
            @error('sessionName')
                <span class="text-error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <input
                    required
                    id="sessionName"
                    wire:model="sessionName"
                    type="text"
                    placeholder="Session name"
                    class="w-full input @error('sessionName') border-error @enderror"
                />
                <button
                    class="btn btn-primary"
                    type="submit"
                >{{ __('Create') }}</button>
            </div>
        </form>
    </div>
</div>
