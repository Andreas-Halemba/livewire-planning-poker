@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endassets

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'href' => route('dashboard')],
            ['label' => 'Session: ' . $session->name, 'href' => route('session.voting', $session->invite_code)],
            ['label' => 'Live Voting'],
        ]" />

        {{-- Session Header --}}
        <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex flex-col gap-3 min-w-0">
                    <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                        Session: <span class="font-bold">{{ $session->name }}</span>
                    </h1>
                    @if($showMyRoleToggle)
                        <div class="flex flex-col items-start gap-1.5">
                            <span class="text-xs font-semibold text-base-content/50 uppercase tracking-wide">Deine Rolle</span>
                            <div class="join join-horizontal shadow-sm">
                                <button
                                    type="button"
                                    wire:click="setMyParticipantRole('voter')"
                                    wire:loading.attr="disabled"
                                    wire:target="setMyParticipantRole"
                                    @class([
                                        'join-item btn btn-sm px-3 sm:px-4',
                                        'btn-primary' => $myParticipantRole === \App\Enums\SessionParticipantRole::Voter,
                                        'btn-ghost bg-base-100 hover:bg-base-200 border border-base-content/15' => $myParticipantRole !== \App\Enums\SessionParticipantRole::Voter,
                                    ])
                                >
                                    Schätzt mit
                                </button>
                                <button
                                    type="button"
                                    wire:click="setMyParticipantRole('viewer')"
                                    wire:loading.attr="disabled"
                                    wire:target="setMyParticipantRole"
                                    @class([
                                        'join-item btn btn-sm px-3 sm:px-4',
                                        'btn-primary' => $myParticipantRole === \App\Enums\SessionParticipantRole::Viewer,
                                        'btn-ghost bg-base-100 hover:bg-base-200 border border-base-content/15' => $myParticipantRole !== \App\Enums\SessionParticipantRole::Viewer,
                                    ])
                                >
                                    Nur zuschauen
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="flex flex-col sm:items-end gap-2 w-full sm:w-auto shrink-0">
                    <div class="text-sm text-base-content/70 text-left sm:text-right">
                        <span class="text-success font-medium">{{ $onlineCount }} online</span> •
                        {{ $participantCount }} Mitglieder •
                        {{ $finishedCount }}/{{ $issueCount }} Issues
                    </div>
                    <a href="{{ route('session.async', $session->invite_code) }}" class="btn btn-sm btn-info btn-outline self-start sm:self-end">
                        Zum Async Voting
                    </a>
                </div>
            </div>

            {{-- Teilnehmer-Liste --}}
            <div class="text-sm font-semibold mt-4 mb-3 uppercase tracking-wide">Teilnehmer</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach($session->users as $user)
                    @php
                        $participantRole = $user->id === $session->owner_id
                            ? null
                            : (\App\Enums\SessionParticipantRole::tryFrom((string) ($user->pivot->role ?? \App\Enums\SessionParticipantRole::Voter->value))
                                ?? \App\Enums\SessionParticipantRole::Voter);
                    @endphp
                    <div wire:key="participant-wrap-{{ $user->id }}" class="min-w-0">
                        <x-v2.participant-card
                            :user="$user"
                            :session="$session"
                            :current-issue="$currentIssue"
                            :online-user-ids="$onlineUserIds"
                            :voted-user-ids="$votedUserIds"
                            :votes-by-user="$votesByUser"
                            :votes-revealed="$votesRevealed"
                            :participant-role="$participantRole"
                        />
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Aktuelles Voting (wenn aktiv) --}}
        @if($currentIssue)
            @include('livewire.v2.partials._voting-panel')
        @endif

        {{-- Issue Listen --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            @include('livewire.v2.partials._issue-list-open')
            @include('livewire.v2.partials._issue-list-finished')
        </div>

        {{-- FAB für Owner --}}
        @if($isOwner)
            <div class="fixed bottom-6 right-6 z-50">
                <label for="add-issue-drawer" class="btn btn-primary btn-circle btn-lg shadow-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </label>
            </div>
        @endif

    </div>

    {{-- Drawer (außerhalb des Layouts) --}}
    @if($isOwner)
        @include('livewire.v2.partials._drawer')
    @endif
</div>
@script
<script>
    // Höre auf das scroll-to-voting-panel Event
    $wire.on('scroll-to-voting-panel', () => {
        setTimeout(() => {
            const panel = document.getElementById('voting-panel');
            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 200);
    });
</script>
@endscript
