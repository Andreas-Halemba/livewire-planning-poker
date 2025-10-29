<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
    @forelse ($participants as $user)
        @php
            $isCurrentUser = $user->id === Auth::id();
            $hasVoted = $this->userDidVote((string) $user->id);
            $isOwner = $user->id === $session->owner_id;
            $votingActive = $session->currentIssue() !== null;
            // Determine status color:
            // gray = no voting active
            // yellow = voting active but user hasn't voted
            // green = voting active and user has voted
            // red = votes revealed and user hasn't voted
            $statusBgColor = 'bg-gray-400';
            $statusBorderColor = 'border-gray-200';
            $cardBgColor = 'bg-gray-50';

            if ($isOwner) {
                $statusBgColor = 'bg-amber-500';
            } elseif (!$votingActive) {
                // No voting active - gray
                $statusBgColor = 'bg-gray-400';
                $cardBgColor = 'bg-gray-50';
                $statusBorderColor = 'border-gray-200';
            } elseif ($votesRevealed && !$hasVoted) {
                // Votes revealed and user hasn't voted - red
                $statusBgColor = 'bg-red-500';
                $cardBgColor = 'bg-red-50';
                $statusBorderColor = 'border-red-500';
            } elseif ($hasVoted) {
                // Voting active and user has voted - green
                $statusBgColor = 'bg-green-500';
                $cardBgColor = 'bg-green-50';
                $statusBorderColor = 'border-green-500';
            } else {
                // Voting active but user hasn't voted - yellow
                $statusBgColor = 'bg-yellow-500';
                $cardBgColor = 'bg-yellow-50';
                $statusBorderColor = 'border-yellow-500';
            }
        @endphp

        <div @class([
            'flex items-center gap-3 p-2.5 rounded-lg border transition-all',
            $cardBgColor,
            $statusBorderColor,
        ]) wire:key="user-{{ $user->id }}">
            <div @class([
                'w-9 h-9 rounded-full text-white text-sm font-semibold flex items-center justify-center flex-shrink-0',
                $statusBgColor,
            ])>
                @if ($isOwner)
                    <span class="text-xs font-bold">PO</span>
                @elseif($hasVoted)
                    <span class="text-lg">✓</span>
                @else
                    <span class="text-base">?</span>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 break-words">
                    {{ $user->name }}
                    @if ($isCurrentUser)
                        <span class="text-xs text-gray-500">(You)</span>
                    @endif
                </div>
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
