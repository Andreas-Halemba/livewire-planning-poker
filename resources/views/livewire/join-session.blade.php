<div class="w-full shadow-xl card bg-base-300 text-base-content">
    <div class="card-body">
        <h2 class="card-title">{{ __('Join an existing session') }}</h2>
        <p>{{ __('Please ask your session owner for the invte code') }}</p>
        <form
            class="w-full space-y-2 form-control"
            wire:submit.prevent="joinSession"
        >
            @error('inviteCode')
                <span class="text-error">{{ $message }}</span>
            @enderror
            <div class="input-group">
                <input
                    required
                    id="inviteCode"
                    wire:model="inviteCode"
                    type="text"
                    placeholder="Invite code"
                    class="w-full input @error('inviteCode') border-error @enderror"
                />
                <button
                    type="submit"
                    class="btn btn-secondary"
                >Join</button>
            </div>
        </form>
    </div>
</div>
