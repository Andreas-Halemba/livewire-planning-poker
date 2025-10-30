<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2" wire:key="participants-grid-{{ $session->id }}">
    @forelse ($participants ?? [] as $user)
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
            $statusBgColor = 'bg-base-300';
            $statusTextColor = 'text-base-content';
            $statusBorderColor = 'border-base-100';
            $cardBgColor = 'bg-base-100';

            if ($isOwner) {
                $statusBgColor = 'bg-accent';
                $statusTextColor = 'text-accent-content';
            } elseif (!$votingActive) {
                // No voting active - gray
                $statusBgColor = 'bg-base-300';
                $statusTextColor = 'text-base-content';
                $cardBgColor = 'bg-base-200';
                $statusBorderColor = 'border-base-300';
            } elseif ($votesRevealed && !$hasVoted) {
                // Votes revealed and user hasn't voted - red
                $statusBgColor = 'bg-error';
                $statusTextColor = 'text-error-content';
                $cardBgColor = 'bg-error/10';
                $statusBorderColor = 'border-error';
            } elseif ($hasVoted) {
                // Voting active and user has voted - green
                $statusBgColor = 'bg-success';
                $statusTextColor = 'text-success-content';
                $cardBgColor = 'bg-success/10';
                $statusBorderColor = 'border-success';
            } else {
                // Voting active but user hasn't voted - yellow
                $statusBgColor = 'bg-warning';
                $statusTextColor = 'text-warning-content';
                $cardBgColor = 'bg-warning/10';
                $statusBorderColor = 'border-warning';
            }
        @endphp

        <div @class([
            'flex items-center gap-3 p-2.5 rounded-lg border transition-all',
            $cardBgColor,
            $statusBorderColor,
        ]) wire:key="user-{{ $user->id }}">
            <div @class([
                'w-9 h-9 rounded-full text-sm font-semibold flex items-center justify-center flex-shrink-0',
                $statusBgColor,
                $statusTextColor,
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
                <div class="text-sm font-medium text-base-content break-words">
                    {{ $user->name }}
                    @if ($isCurrentUser)
                        <span class="text-xs text-base-content/60">(You)</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="rounded-lg bg-warning/10 border border-warning/30 p-4 flex items-center gap-3">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="text-warning">No users in this session.</span>
            </div>
        </div>
    @endforelse
</div>
