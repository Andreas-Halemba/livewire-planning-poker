<div class="w-full shadow-xl card bg-base-300 text-base-content">
    <div class="card-body">
        <h2 class="card-title">{{ __('Join an existing session') }}</h2>
        <p>{{ __('Please ask your session owner for the invite code') }}</p>
        <form class="w-full space-y-2" wire:submit="joinSession">
            @error('inviteCode')
                <span class="text-error">{{ $message }}</span>
            @enderror
            <div class="join w-full">
                <input required id="inviteCode" wire:model.live="inviteCode" type="text" placeholder="Invite code"
                    class="join-item input flex-1 @error('inviteCode') border-error @enderror" />
                <button type="submit" class="join-item btn btn-secondary">Join</button>
            </div>
        </form>
    </div>
</div>
