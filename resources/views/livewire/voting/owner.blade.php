<div>
    @php
        $openIssues = $issues->where('status', '!=', Issue::STATUS_FINISHED);
        $estimatedIssues = $issues->where('status', Issue::STATUS_FINISHED);
    @endphp

    <!-- Product Owner Panel -->
    @if($currentIssue)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border-2 border-amber-500">
            <div class="flex items-center gap-3 mb-5">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-amber-500 text-white uppercase">Product
                    Owner</span>
                <h2 class="text-lg font-semibold text-gray-900">Estimation Control</h2>
            </div>

            <!-- Current Issue Display -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-5">
                <div class="text-xs font-semibold uppercase text-amber-900 mb-2">Aktuell zu schätzen</div>
                <div class="text-xl font-bold mb-1 text-gray-900">{!! $currentIssue->title_html !!}</div>
                <div class="text-sm text-gray-600">
                    {{ $currentIssue->description ? Str::limit($currentIssue->description, 100) : 'Keine Beschreibung' }}
                </div>
            </div>

            <!-- Reveal Votes Button -->
            <div class="mb-5">
                @php
                    $hasVotes = $currentIssue->votes()->whereNotNull('value')->exists();
                @endphp
                @if($hasVotes && !$votesRevealed)
                    <button wire:click="revealVotes"
                        class="w-full px-5 py-3.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Votes revealen
                    </button>
                @elseif($votesRevealed)
                    <button
                        class="w-full px-5 py-3.5 bg-gray-300 text-gray-500 font-semibold rounded-lg cursor-not-allowed flex items-center justify-center gap-2"
                        disabled>
                        Votes revealed
                    </button>
                @else
                    <button
                        class="w-full px-5 py-3.5 bg-gray-300 text-gray-500 font-semibold rounded-lg cursor-not-allowed flex items-center justify-center gap-2"
                        disabled>
                        Warte auf Votes...
                    </button>
                @endif
            </div>

            <!-- Vote Results (shown after reveal) -->
            @if($votesRevealed && !empty($groupedVotes))
                <div class="mb-5">
                    <div class="text-sm font-semibold text-gray-600 mb-3">Schätzungen der Team-Mitglieder</div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4">
                        @foreach($groupedVotes as $value => $data)
                            <div @class([
                                'bg-gray-50 border-2 rounded-lg p-4 text-center cursor-pointer transition-all hover:border-blue-500 hover:bg-blue-50',
                                'border-gray-300' => $selectedEstimate !== $value,
                                'bg-green-50 border-green-500 shadow-md' => $selectedEstimate === $value,
                            ])
                                wire:click="selectEstimate('{{ $value }}')" wire:key="estimate-{{ $value }}">
                                <div class="text-3xl font-bold mb-1 text-gray-900">{{ $value }}</div>
                                <div class="text-xs text-gray-600">
                                    {{ $data['count'] }} {{ $data['count'] === 1 ? 'Stimme' : 'Stimmen' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ implode(', ', $data['participants']) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Custom Estimate Input -->
                    <div class="flex flex-col sm:flex-row gap-3 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Oder manuell eingeben:</label>
                            <input type="number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                wire:model.live="customEstimate" placeholder="z.B. 3, 5, 8, 13..." min="0" />
                        </div>
                        <button wire:click="confirmEstimate({{ $currentIssue->id }})"
                            class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors whitespace-nowrap">
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
        <div class="flex flex-col gap-4 lg:order-1">
            <!-- Import Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Issues importieren
                </h3>
                <livewire:jira-import :session="$session" />
            </div>

            <!-- Manual Add Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Manuell hinzufügen
                </h3>
                <form wire:submit="addIssue()">
                    <div class="mb-3">
                        <input type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            wire:model.live="issueTitle" placeholder="Issue-Titel" required />
                        @error('issueTitle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <textarea
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-20"
                            wire:model.live="issueDescription" placeholder="Beschreibung (optional)"></textarea>
                        @error('issueDescription')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
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
        <div class="flex flex-col gap-5 lg:order-2 min-w-0">
            <!-- Tabs -->
            <div class="bg-white rounded-xl shadow-sm p-1 flex gap-1">
                <button @class([
                    'flex-1 px-5 py-3 rounded-lg font-medium transition-colors',
                    'bg-blue-500 text-white' => $activeTab !== 'estimated',
                    'text-gray-600 hover:bg-gray-100' => $activeTab === 'estimated',
                ])
                    wire:click="$set('activeTab', 'open')">
                    Offene Issues
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-white/20 text-white">{{ $openIssues->count() }}</span>
                </button>
                <button @class([
                    'flex-1 px-5 py-3 rounded-lg font-medium transition-colors',
                    'bg-blue-500 text-white' => $activeTab === 'estimated',
                    'text-gray-600 hover:bg-gray-100' => $activeTab !== 'estimated',
                ])
                    wire:click="$set('activeTab', 'estimated')">
                    Geschätzte Issues
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-white/20 text-white">{{ $estimatedIssues->count() }}</span>
                </button>
            </div>

            <!-- Open Issues List -->
            @if($activeTab !== 'estimated')
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        @forelse($openIssues as $index => $issue)
                            <div @class([
                                'flex items-center gap-3 p-4 transition-colors hover:bg-gray-50',
                                'bg-amber-50 border-l-4 border-l-amber-500' => $issue->isVoting(),
                            ]) wire:key="open-issue-{{ $issue->id }}">
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <div class="text-sm font-semibold text-blue-600 mb-1 break-words">
                                        {!! $issue->title_html !!}
                                    </div>
                                    <div class="text-sm text-gray-600 truncate">
                                        {{ $issue->description ? Str::limit($issue->description, 80) : 'Keine Beschreibung' }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($issue->status === Issue::STATUS_VOTING)
                                        <span class="px-3 py-1 text-sm bg-amber-100 text-amber-800 font-medium rounded">
                                            Voting...
                                        </span>
                                        <button type="button" wire:click="cancelIssue({{ $issue->id }})"
                                            class="px-3 py-1 text-sm bg-red-500 hover:bg-red-600 text-white font-medium rounded transition-colors">
                                            Cancel
                                        </button>
                                    @else
                                        <button wire:click="voteIssue({{ $issue->id }})"
                                            class="px-3 py-1.5 text-sm bg-green-500 hover:bg-green-600 text-white font-medium rounded transition-colors">
                                            Vote now
                                        </button>
                                    @endif
                                    <button wire:click="deleteIssue({{ $issue->id }})"
                                        wire:confirm="Are you sure you want to delete this issue?"
                                        class="w-8 h-8 flex items-center justify-center bg-red-100 hover:bg-red-200 text-red-600 rounded font-bold transition-colors">
                                        ×
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                <div class="text-4xl mb-2">📋</div>
                                <div>Keine offenen Issues</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Estimated Issues List -->
            @if($activeTab === 'estimated')
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        @forelse($estimatedIssues as $issue)
                            <div class="flex items-center gap-3 p-4 transition-colors hover:bg-gray-50"
                                wire:key="estimated-issue-{{ $issue->id }}">
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <div class="text-sm font-semibold text-blue-600 mb-1 break-words">
                                        {!! $issue->title_html !!}
                                    </div>
                                    <div class="text-sm text-gray-600 truncate">
                                        {{ $issue->description ? Str::limit($issue->description, 80) : 'Keine Beschreibung' }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm font-semibold bg-green-500 text-white">
                                        {{ $issue->storypoints ?? 'X' }} SP
                                    </span>
                                    <button wire:click="deleteIssue({{ $issue->id }})"
                                        wire:confirm="Are you sure you want to delete this issue?"
                                        class="w-8 h-8 flex items-center justify-center bg-red-100 hover:bg-red-200 text-red-600 rounded font-bold transition-colors">
                                        ×
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                <div class="text-4xl mb-2">✅</div>
                                <div>Keine geschätzten Issues</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
