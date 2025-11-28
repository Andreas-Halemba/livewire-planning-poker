<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- V2 Preview Alert -->
    <div role="alert" class="alert alert-info alert-vertical sm:alert-horizontal mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current h-6 w-6 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <div>
            <h3 class="font-bold">Neue Version verfügbar!</h3>
            <div class="text-xs">Teste die V2 mit verbessertem UI, Keyboard-Shortcuts und Jira-Import.</div>
        </div>
        <a href="{{ route('session.v2', $session->invite_code) }}" class="btn btn-sm">
            V2 testen
        </a>
    </div>

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
        <livewire:session-participants :session="$session" :key="'session-participants-'.$session->id" />
    </div>

    @can('vote_session', $session)
        @if(Auth::id() !== $session->owner_id)
            @php
                $currentIssue = $session->currentIssue();
            @endphp

            <!-- Voting Cards Section - Shows for active voting or manually selected issue -->
            <livewire:voting-cards :session="$session" key="voting-cards-{{ $session->id }}" />

            <!-- Upcoming Issues & History -->
            <livewire:voting.voter :session="$session" :key="'voter-'.$session->id" />
        @endif
    @endcan

    @can('owns_session', $session)
        <livewire:voting.owner :session="$session" :key="'owner-'.$session->id" />
    @endcan
</div>