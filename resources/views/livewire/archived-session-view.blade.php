<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Session: ' . $session->name, 'href' => route('session.archived', $session->invite_code)],
        ['label' => 'Archived'],
    ]" />

    {{-- Session Header (v2/async-like) --}}
    <div class="bg-base-300 rounded-xl shadow-md border border-base-300 p-5 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <div class="text-xs uppercase tracking-wide text-base-content/60 mb-1 badge badge-warning text-warning-content">Archivierte Session</div>
                <h1 class="text-xl sm:text-2xl font-semibold text-base-content">
                    Session: <span class="font-bold">{{ $session->name }}</span>
                </h1>
                <div class="mt-1 text-xs sm:text-sm text-base-content/70">
                    Archiviert am {{ optional($session->archived_at)->toDayDateTimeString() }} •
                    Schreibgeschützt (read-only)
                </div>
            </div>

            <div class="flex flex-col sm:items-end gap-2">
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline">
                        Zur Übersicht
                    </a>
                    @if($isOwner)
                        <button wire:click="unarchiveSession" class="btn btn-sm btn-success">
                            Reaktivieren
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Issues (read-only) --}}
    <div class="card bg-base-200 shadow-lg border border-base-300">
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base sm:text-lg">
                Issues
                <span class="badge badge-outline text-base-content/70">{{ $session->issues->count() }}</span>
            </h2>

            <div class="mt-3 space-y-3">
                @forelse ($session->issues as $issue)
                    <div class="border border-base-300 bg-base-200 rounded-xl p-4 sm:p-5"
                        wire:key="archived-issue-{{ $issue->id }}">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                @if($issue->jira_url || $issue->jira_key)
                                    <a href="{{ $issue->getJiraBrowserUrl() }}" target="_blank" rel="nofollow"
                                        class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-0.5">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z" />
                                        </svg>
                                        {{ $issue->jira_key ?? 'Jira öffnen' }}
                                    </a>
                                @endif
                                <div class="text-sm sm:text-base font-semibold text-base-content break-words">
                                    {{ $issue->title }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 sm:justify-end sm:shrink-0">
                                <span class="badge badge-success badge-outline whitespace-nowrap">
                                    {{ $issue->storypoints ?? '—' }} SP
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-base-content/60">
                        Keine Issues in dieser Session vorhanden.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

