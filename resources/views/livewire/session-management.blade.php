<div class="bg-base-100">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <livewire:create-session />
        <livewire:join-session />
        @if (auth()->user()->sessions->count() > 0)
            <div class="col-span-full">
                <livewire:user-sessions />
            </div>
        @endif
        @if (auth()->user()->ownedSessions->count() > 0)
            <div class="col-span-full">
                <livewire:owner-sessions />
            </div>
        @endif
    </div>

</div>
