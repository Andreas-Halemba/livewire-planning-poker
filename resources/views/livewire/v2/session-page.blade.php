@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endassets

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- V2 Preview Alert --}}
        <div role="alert" class="alert alert-info alert-outline alert-vertical sm:alert-horizontal mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                class="stroke-current h-6 w-6 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="font-bold">V2 Preview</h3>
                <div class="text-xs">Diese Version ist noch in Entwicklung. Bei Problemen zur stabilen V1 wechseln.
                </div>
            </div>
            <a href="{{ route('session.voting', $session->invite_code) }}" class="btn btn-sm btn-info btn-outline">
                Zur V1
            </a>
        </div>

        {{-- Session Header --}}
        <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                    Session: <span class="font-bold">{{ $session->name }}</span>
                </h1>
                <div class="text-sm text-base-content/70">
                    <span class="text-success font-medium">{{ $onlineCount }} online</span> •
                    {{ $participantCount }} Mitglieder •
                    {{ $finishedCount }}/{{ $issueCount }} Issues
                </div>
            </div>

            {{-- Teilnehmer-Liste --}}
            <div class="text-sm font-semibold mt-4 mb-3 uppercase tracking-wide">Teilnehmer</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach($session->users as $user)
                    <x-v2.participant-card wire:key="participant-{{ $user->id }}" :user="$user" :session="$session" :current-issue="$currentIssue"
                        :online-user-ids="$onlineUserIds" :voted-user-ids="$votedUserIds" :votes-by-user="$votesByUser"
                        :votes-revealed="$votesRevealed" />
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
