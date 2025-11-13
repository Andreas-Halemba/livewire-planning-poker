<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Session Header -->
    <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                Session: <span class="font-bold">{{ $session->name }}</span>
            </h1>
            <div class="text-sm text-base-content/70">
                {{ $participantsCount }} Teilnehmer •
                {{ $session->issues->where('status', 'finished')->count() }} von {{ $session->issues->count() }} Issues
                geschätzt
            </div>
        </div>
        <div class="text-sm font-semibold mt-3 mb-3 uppercase tracking-wide">Teilnehmer</div>
        <livewire:session-participants :session="$session" :key="'session-participants-' . $session->id" />
    </div>

    @can('vote_session', $session)
        @if(Auth::id() !== $session->owner_id)
            @php
                $currentIssue = $session->currentIssue();
            @endphp

            <!-- Voting Cards Section - Shows for active voting or manually selected issue -->
            <livewire:voting-cards :session="$session" key="voting-cards-{{ $session->id }}" />

            <!-- Upcoming Issues & History -->
            <livewire:voting.voter :session="$session" :key="'voter-' . $session->id" />
        @endif
    @endcan

    @can('owns_session', $session)
        <livewire:voting.owner :session="$session" :key="'owner-' . $session->id" />
    @endcan
</div>