<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
    @forelse ($participants as $user)
        @php
            $isCurrentUser = $user->id === Auth::id();
            $hasVoted = $this->userDidVote((string) $user->id);
            $isOwner = $user->id === $session->owner_id;
            $initials = strtoupper(substr($user->name, 0, 2));
        @endphp

        <div @class([
            'flex items-center gap-3 p-2.5 rounded-lg border transition-all',
            'bg-gray-50 border-gray-200' => !$hasVoted && !$isCurrentUser,
            'bg-green-50 border-green-500' => $hasVoted,
            'bg-blue-50 border-blue-500' => $isCurrentUser && !$hasVoted,
        ]) wire:key="user-{{ $user->id }}">
            <div @class([
                'w-9 h-9 rounded-full text-white text-sm font-semibold flex items-center justify-center flex-shrink-0',
                'bg-green-500' => $hasVoted,
                'bg-blue-500' => $isCurrentUser && !$hasVoted,
                'bg-gray-400' => !$hasVoted && !$isCurrentUser,
            ])>
                <span>{{ $initials }}</span>
            </div>

            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 truncate">
                    {{ $user->name }}
                    @if ($isCurrentUser)
                        <span class="text-xs text-gray-500">(You)</span>
                    @endif
                </div>
            </div>

            <div class="flex-shrink-0">
                @if ($isOwner)
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-500 text-white uppercase">PO</span>
                @elseif($hasVoted)
                    <span class="text-green-500 text-xl">✓</span>
                @else
                    <span class="text-gray-400 text-base">?</span>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 flex items-center gap-3">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="text-amber-800">No users in this session.</span>
            </div>
        </div>
    @endforelse
</div>
