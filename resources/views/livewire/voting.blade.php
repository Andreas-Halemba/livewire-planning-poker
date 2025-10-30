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
        <livewire:session-participants :session="$session" />
    </div>

    @can('vote_session', $session)
        @if(Auth::id() !== $session->owner_id)
            @php
                $currentIssue = $session->currentIssue();
            @endphp

            <!-- Voting Cards Section - Shows for active voting or manually selected issue -->
            <livewire:voting-cards :session="$session" key="voting-cards-{{ $session->id }}" />

            <!-- Current Issue Card - Only show if there's an active voting issue -->
            @if($currentIssue)
                <div class="bg-base-100 rounded-xl shadow-md p-6 sm:p-8 mb-6 border-2 border-primary"
                    x-data="{ descriptionOpen: false }">
                    <div class="text-xs font-semibold text-primary uppercase tracking-wide mb-3">Aktuell zu schätzen</div>
                    <div class="text-xl font-bold mb-1 text-base-content">{!! $currentIssue->title_html !!}</div>
                    @if($currentIssue->description)
                        <div class="mb-4">
                            <button @click="descriptionOpen = !descriptionOpen"
                                class="flex items-center gap-2 text-sm text-primary hover:text-primary/80 font-medium transition-colors">
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': descriptionOpen }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                                <span x-text="descriptionOpen ? 'Beschreibung ausblenden' : 'Beschreibung anzeigen'"></span>
                            </button>
                            <div x-show="descriptionOpen" x-collapse
                                class="transition-all mt-3 prose prose-sm max-w-none bg-white/90 text-black p-4 rounded-lg prose-a:text-accent prose-headings:text-black border border-accent">
                                {!! $currentIssue->formatted_description !!}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Upcoming Issues & History -->
            <livewire:voting.voter :session="$session" />
        @endif
    @endcan

    @can('owns_session', $session)
        <livewire:voting.owner :session="$session" />
    @endcan
</div>
