<div
    x-data="{
        sessionInviteCode: '{{ $session->invite_code }}',
        ownerId: {{ $session->owner_id }},
        currentUserId: {{ Auth::id() ?? 0 }},
        sessionPath: '{{ parse_url(route('session.voting', ['inviteCode' => $session->invite_code]), PHP_URL_PATH) }}',
        isLeavingSession: false,
        init() {
            // Fallback: Cancel voting when owner leaves the session (especially if PO is alone)
            // This ensures voting is canceled even if no other participants are present
            
            // Listen for link clicks that leave the session page
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (link && link.href) {
                    try {
                        const linkUrl = new URL(link.href, window.location.origin);
                        const currentPath = window.location.pathname;
                        // Only set flag if clicking a link that navigates away from session page
                        if (linkUrl.pathname !== currentPath && currentPath === this.sessionPath) {
                            this.isLeavingSession = true;
                        }
                    } catch (err) {
                        // Invalid URL, ignore
                    }
                }
            }, true);
            
            // Listen for beforeunload - only cancel if actually leaving session (not refresh)
            window.addEventListener('beforeunload', (e) => {
                // Only cancel if we detected navigation away from session page
                if (this.ownerId === this.currentUserId && this.isLeavingSession) {
                    // Use sendBeacon for reliable delivery even during page unload
                    const url = '{{ route('api.sessions.cancel-voting-on-leave', ['inviteCode' => $session->invite_code]) }}';
                    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
                    
                    // navigator.sendBeacon is more reliable than fetch during page unload
                    // We need to send CSRF token as form data since sendBeacon doesn't support custom headers
                    if (navigator.sendBeacon) {
                        const formData = new FormData();
                        formData.append('_token', csrfToken);
                        navigator.sendBeacon(url, formData);
                    } else {
                        // Fallback for older browsers
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({}),
                            keepalive: true
                        }).catch(() => {});
                    }
                }
            });
        }
    }">
    @php
        $openIssues = $issues->where('status', '!=', Issue::STATUS_FINISHED);
        $estimatedIssues = $issues->where('status', Issue::STATUS_FINISHED);
    @endphp

    <!-- Product Owner Panel -->
    @if($currentIssue)
        <div class="bg-base-200 rounded-xl shadow-md p-6 mb-6 border border-info">
            <div class="flex items-center gap-3 mb-5">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-info text-info-content uppercase">Product
                    Owner Panel</span>
            </div>

            <!-- Current Issue Display -->
            <div class="bg-info/10 border border-info/30 rounded-lg p-4 mb-5" x-data="{ descriptionOpen: false }">
                <div class="text-xs font-semibold uppercase text-info mb-2">Aktuell zu schätzen</div>
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
                            class="mt-3 prose prose-sm max-w-none bg-white/90 text-black p-4 rounded-lg prose-a:text-accent prose-headings:text-black border border-accent">
                            {!! $currentIssue->formatted_description !!}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Reveal Votes Button -->
            <div class="mb-3">
                @php
                    $hasVotes = $currentIssue->votes()->whereNotNull('value')->exists();
                @endphp
                @if($hasVotes && !$votesRevealed)
                    <button wire:click="revealVotes"
                        class="w-full px-5 py-3.5 btn btn-success cursor-pointer hover:bg-success/90 font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Schätzungen anzeigen
                    </button>
                @elseif($votesRevealed)
                    <button
                        class="w-full px-5 py-3.5 bg-base-300 text-base-content/60 font-semibold rounded-lg cursor-not-allowed flex items-center justify-center gap-2"
                        disabled>
                        Schätzungen angezeigt
                    </button>
                @else
                    <button
                        class="w-full px-5 py-3.5 bg-base-300 text-base-content/60 font-semibold rounded-lg cursor-not-allowed flex items-center justify-center gap-2"
                        disabled>
                        Warte auf Schätzungen...
                    </button>
                @endif
            </div>

            <!-- Cancel Voting Button -->
            <div class="mb-5">
                <button type="button" wire:click="cancelIssue({{ $currentIssue->id }})"
                    class="w-full px-5 py-3.5 bg-error hover:bg-error/90 text-error-content font-semibold rounded-lg transition-colors cursor-pointer">
                    Schätzung abbrechen
                </button>
            </div>

            <!-- Vote Results (shown after reveal) -->
            @if($votesRevealed && !empty($groupedVotes))
                <div class="mb-5">
                    <div class="text-sm font-semibold text-base-content/70 mb-3">Schätzungen der Team-Mitglieder</div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4">
                        @foreach($groupedVotes as $value => $data)
                            @php
                                $isSelected = (string) $selectedEstimate === (string) $value;
                            @endphp
                            <div @class([
                                'border-2 rounded-lg p-4 text-center cursor-pointer transition-all',
                                'bg-base-200 border-base-300 hover:border-primary hover:bg-primary/10' => !$isSelected,
                                'bg-success border-success shadow-md hover:bg-success/90' => $isSelected,
                            ]) wire:click="selectEstimate('{{ $value }}')"
                                wire:key="estimate-{{ $value }}">
                                <div @class([
                                    'text-3xl font-bold mb-1',
                                    'text-base-content' => !$isSelected,
                                    'text-success-content' => $isSelected,
                                ])>{{ $value }}</div>
                                <div @class([
                                    'text-xs',
                                    'text-base-content/70' => !$isSelected,
                                    'text-success-content/90' => $isSelected,
                                ])>
                                    {{ $data['count'] }} {{ $data['count'] === 1 ? 'Stimme' : 'Stimmen' }}
                                </div>
                                <div @class([
                                    'text-xs mt-1',
                                    'text-base-content/60' => !$isSelected,
                                    'text-success-content/80' => $isSelected,
                                ])>
                                    {{ implode(', ', $data['participants']) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Custom Estimate Input -->
                    <div class="flex flex-col sm:flex-row gap-3 items-end">
                        <div class="flex-1 w-1/2">
                            <label class="block text-sm font-medium text-base-content mb-1">Oder manuell
                                eingeben:</label>
                            <input type="number"
                                class="w-full px-3 py-2 border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-base-100 text-base-content"
                                wire:model.live="customEstimate" placeholder="z.B. 3, 5, 8, 13..." min="0" />
                        </div>
                        <button wire:click="confirmEstimate({{ $currentIssue->id }})"
                            class="px-6 py-2 w-1/2 btn btn-success cursor-pointer font-semibold rounded-lg transition-colors whitespace-nowrap">
                            Schätzung übernehmen
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-6 mb-6 overflow-hidden">
        <!-- Left Sidebar: Actions -->
        <div class="flex flex-col order-2 gap-4 lg:order-1">
            <!-- Import Section -->
            <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-6">
                <h3 class="text-base font-semibold text-base-content mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/60" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Issues importieren
                </h3>
                <livewire:jira-import :session="$session" />
            </div>

            <!-- Manual Add Section -->
            <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-6">
                <h3 class="text-base font-semibold text-base-content mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/60" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Manuell hinzufügen
                </h3>
                <form wire:submit="addIssue()">
                    <div class="mb-3">
                        <input type="text"
                            class="w-full px-3 py-2 border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-base-100 text-base-content"
                            wire:model.live="issueTitle" placeholder="Issue-Titel" required />
                        @error('issueTitle')
                            <p class="mt-1 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <textarea
                            class="w-full px-3 py-2 border border-base-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary min-h-20 bg-base-100 text-base-content"
                            wire:model.live="issueDescription" placeholder="Beschreibung (optional)"></textarea>
                        @error('issueDescription')
                            <p class="mt-1 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-accent btn-block cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Issue hinzufügen
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Issue Management -->
        <div class="flex flex-col order-1 gap-5 lg:order-2 min-w-0">
            <!-- Tabs -->
            <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-1 flex gap-1">
                <button @class([
                    'btn flex-1 flex items-center justify-center px-5 py-3 rounded-lg font-medium transition-colors cursor-pointer',
                    'btn-primary' => $activeTab !== 'estimated',
                    'btn-ghost text-base-content/70' => $activeTab === 'estimated',
                ])    wire:click="$set('activeTab', 'open')">
                    Offene Issues
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-secondary text-secondary-content">{{ $openIssues->count() }}</span>
                </button>
                <button @class([
                    'btn flex-1 flex items-center justify-center px-5 py-3 rounded-lg font-medium transition-colors cursor-pointer',
                    'btn-primary' => $activeTab === 'estimated',
                    'btn-ghost text-base-content/70' => $activeTab !== 'estimated',
                ])    wire:click="$set('activeTab', 'estimated')">
                    Geschätzte Issues
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-secondary text-secondary-content">{{ $estimatedIssues->count() }}</span>
                </button>
            </div>

            <!-- Open Issues List -->
            @if($activeTab !== 'estimated')
                <div class="bg-base-200 rounded-xl shadow-md border border-base-300 overflow-hidden">
                    <div class="divide-y divide-base-300">
                        @forelse($openIssues as $index => $issue)
                            <div @class([
                                'bg-base-200 flex items-center gap-3 p-4 transition-colors text-base-content hover:text-base-content/80 hover:bg-base-300 cursor-pointer',
                                'bg-warning/10 border-l-4 border-l-warning text-warning-content hover:bg-warning/20' => $issue->isVoting(),
                            ]) wire:key="open-issue-{{ $issue->id }}">
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <div class="text-sm font-semibold mb-1 break-words">
                                        {!! $issue->title_html !!}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($issue->status === Issue::STATUS_VOTING)
                                        <span class="px-3 py-1 text-sm bg-warning/40 text-warning-content font-medium rounded">
                                            Schätzung läuft...
                                        </span>
                                    @else
                                        <button wire:click="voteIssue({{ $issue->id }})"
                                            class="btn btn-primary btn-sm cursor-pointer font-medium rounded transition-colors">
                                            Schätzen
                                        </button>
                                    @endif
                                    <button wire:click="deleteIssue({{ $issue->id }})"
                                        wire:confirm="Are you sure you want to delete this issue?"
                                        @disabled($issue->status === Issue::STATUS_VOTING)
                                        @class([
                                            'w-8 h-8 flex items-center justify-center rounded font-bold transition-colors',
                                            'bg-error/20 hover:bg-error/30 text-error cursor-pointer' => $issue->status !== Issue::STATUS_VOTING,
                                            'bg-base-300 text-base-content/30 cursor-not-allowed' => $issue->status === Issue::STATUS_VOTING,
                                        ])>
                                        ×
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-base-content/60">
                                <div class="text-4xl mb-2">📋</div>
                                <div>Keine offenen Issues</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Estimated Issues List -->
            @if($activeTab === 'estimated')
                <div class="bg-base-200 rounded-xl shadow-md border border-base-300 overflow-hidden">
                    <div class="divide-y divide-base-300">
                        @forelse($estimatedIssues as $issue)
                            <div class="flex items-center gap-3 p-4 transition-colors hover:bg-base-300"
                                wire:key="estimated-issue-{{ $issue->id }}">
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <div class="text-sm font-semibold text-primary mb-1 break-words">
                                        {!! $issue->title_html !!}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm font-semibold bg-success text-success-content">
                                        {{ $issue->storypoints ?? 'X' }} SP
                                    </span>
                                    <button wire:click="deleteIssue({{ $issue->id }})"
                                        wire:confirm="Are you sure you want to delete this issue?"
                                        class="w-8 h-8 flex items-center justify-center bg-error/20 hover:bg-error/30 text-error rounded font-bold transition-colors">
                                        ×
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-base-content/60">
                                <div>Keine geschätzten Issues</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
