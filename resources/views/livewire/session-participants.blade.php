<div
    class="grid w-full grid-cols-2 gap-5 p-4 mb-10 sm:grid-cols-3 md:grid-cols-5 bg-base-300 text-base-content rounded-box">
    <h2 class="text-lg font-bold col-span-full">Participants</h2>
    @forelse ($participants as $user)
        <div
            @class([
                'card bg-base-100 text-base-content border-info border',
                'border-success' => $this->userDidVote((string) $user['id']),
            ])
            wire:key="user-{{ $user['id'] }}"
        >
            <div class="items-center justify-between text-center card-body">
                <div class="card-title">{{ $user['name'] }}</div>
                <div @class([
                    'h-20 text-3xl badge badge-ghost aspect-square border border-info',
                    '!badge-success' => $this->userDidVote((string) $user['id']),
                ])>
                    @if ($user['id'] === $session->owner_id)
                        PO
                    @elseif(($votesRevealed || $user['id'] === Auth::id()) && isset($votes[$user['id']]))
                        {{ $votes[$user['id']]}}
                    @else
                        ?
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
        @if (array_filter($votes))
            <button
                wire:click="sendRevealEvent"
                class="order-last btn btn-success col-span-full"
            >Reveal votes</button>
        @endunless
    @endcan
</div>
