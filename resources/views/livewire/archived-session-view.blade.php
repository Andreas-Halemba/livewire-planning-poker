<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-6">
    <div class="bg-base-200 border border-base-300 rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-wide text-base-content/60 mb-1">Archivierte Session</p>
                <h1 class="text-2xl font-semibold text-base-content">{{ $session->name }}</h1>
                <p class="text-base-content/70 text-sm mt-1">
                    Archiviert am {{ optional($session->archived_at)->toDayDateTimeString() }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($isOwner)
                    <button wire:click="unarchiveSession"
                        class="btn btn-primary btn-sm cursor-pointer">
                        Session reaktivieren
                    </button>
                @endif
                <span class="badge badge-outline text-base-content/70">
                    {{ $session->issues->count() }} Issues
                </span>
            </div>
        </div>
        <p class="mt-4 text-base-content/70 text-sm">
            Diese Session ist schreibgeschützt. Es können keine neuen Issues hinzugefügt oder Votes geändert werden.
        </p>
    </div>

    <div class="space-y-4">
        @forelse ($session->issues as $issue)
            <div class="collapse collapse-arrow border border-base-300 bg-base-100 rounded-xl shadow-sm"
                wire:key="archived-issue-{{ $issue->id }}">
                <input type="checkbox" />
                <div class="collapse-title flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div class="text-left">
                        <span class="text-sm font-semibold text-base-content/70">{{ $issue->jira_key ?? 'Issue' }}</span>
                        <div class="text-lg font-semibold text-base-content leading-snug">
                            {!! $issue->title_html !!}
                        </div>
                    </div>
                    <div class="flex items-center gap-3 md:flex-shrink-0">
                        <span class="badge badge-success badge-lg text-lg text-success-content font-semibold px-4">
                            {{ $issue->storypoints ?? '—' }} SP
                        </span>
                        @if ($issue->jira_url ?? $issue->jira_key)
                            <a href="{{ $issue->getJiraBrowserUrl() }}" target="_blank"
                                class="btn btn-sm btn-ghost text-base-content/70 hover:text-primary cursor-pointer">
                                Jira öffnen
                            </a>
                        @endif
                    </div>
                </div>
                <div class="collapse-content border-t border-base-200 bg-base-100/80">
                    @if ($issue->formatted_description)
                        <div class="prose max-w-none text-base-content/80 prose-sm prose-a:text-primary">
                            {!! $issue->formatted_description !!}
                        </div>
                    @else
                        <p class="text-base-content/60 text-sm">Keine Beschreibung verfügbar.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-base-200 rounded-xl border border-base-300 p-6 text-center text-base-content/70">
                Keine Issues in dieser Session vorhanden.
            </div>
        @endforelse
    </div>
</div>

