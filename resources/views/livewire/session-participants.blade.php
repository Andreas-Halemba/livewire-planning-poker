<div
    class="grid w-full grid-cols-2 gap-5 p-4 mb-10 sm:grid-cols-3 md:grid-cols-5 bg-base-300 text-base-content rounded-box">
    <h2 class="text-lg col-span-full">Participants</h2>
    @forelse ($participants as $user)
        <div
            @class([
                'card bg-neutral text-neutral-content border-transparent border',
                'border-success' => $this->userDidVote((string) $user['id']),
            ])
            wire:key="user-{{ $user['id'] }}"
        >
            <div class="items-center justify-between text-center card-body">
                <div class="card-title">{{ $user['name'] }}</div>
                <div @class([
                    'h-20 text-3xl badge badge-ghost aspect-square',
                    '!badge-success' => $this->userDidVote((string) $user['id']),
                ])>
                    @if ($user['id'] === $session->owner_id)
                        PO
                    @else
                        {{ $votes[$user['id']] ?? 'X' }}
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-warning">
            No users in this session.
        </div>
    @endforelse
    @can('owns_session', $session)
        @unless (blank($participantsVoted))
            <button
                wire:click="sendRevealEvent"
                class="order-last btn btn-success col-span-full"
            >Reveal votes</button>
        @endunless
    @endcan
</div>
