<div>
    @if($currentIssue)
        <!-- Issue Card - Always show at top -->
        <div class="bg-base-100 rounded-xl shadow-md p-6 sm:p-8 mb-6 border-2 border-accent"
            x-data="{ descriptionOpen: false }">
            <div class="text-xs font-semibold text-accent uppercase tracking-wide mb-3">
                @if($currentIssue->status === \App\Enums\IssueStatus::VOTING)
                    Aktuell zu schätzen
                @else
                    Asynchron schätzen
                @endif
            </div>
            @if($currentIssue->jira_url && $currentIssue->jira_key)
                <a href="{{ $currentIssue->getJiraBrowserUrl() }}" target="_blank"
                    class="text-base font-bold text-accent hover:text-accent/80 hover:underline mb-2 block">
                    {{ $currentIssue->jira_key }}
                </a>
            @else
                <div class="text-base font-bold text-base-content mb-2">{{ $currentIssue->jira_key ?? 'Issue' }}</div>
            @endif
            @if($currentIssue->jira_url && $currentIssue->jira_key)
                <a href="{{ $currentIssue->getJiraBrowserUrl() }}" target="_blank"
                    class="text-xl font-semibold text-accent hover:text-accent/80 hover:underline mb-4 leading-relaxed block">
                    {{ $currentIssue->title }}
                </a>
            @elseif(Str::startsWith($currentIssue->title, 'https://'))
                <a href="{{ $currentIssue->title }}" target="_blank"
                    class="text-xl font-semibold hover:text-accent/80 hover:underline text-base-content mb-4 leading-relaxed">
                    {{ $currentIssue->title }}
                </a>
            @else
                <div class="text-xl font-semibold text-base-content mb-4 leading-relaxed">{{ $currentIssue->title }}</div>
            @endif
            @if($currentIssue->description)
                <div class="mb-4">
                    <button @click="descriptionOpen = !descriptionOpen"
                        class="flex items-center gap-2 text-sm text-accent hover:text-accent/80 font-medium transition-colors">
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

        <!-- Vote Results (shown after reveal) - Before voting cards -->
        @if($votesRevealed && ! empty($groupedVotes))
            <div class="bg-base-200 rounded-xl shadow-md border border-info p-6 sm:p-8 mb-6">
                <div class="text-center mb-4">
                    <div class="text-lg sm:text-xl font-semibold text-base-content mb-2">Schätzungen der Team-Mitglieder</div>
                    <div class="text-sm text-base-content/70">Der Product Owner wählt nun die finale Schätzung aus</div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($groupedVotes as $value => $data)
                        <div class="border-2 rounded-lg p-4 text-center transition-all bg-base-100 border-base-300">
                            <div class="text-3xl font-bold mb-1 text-base-content">{{ $value }}</div>
                            <div class="text-xs text-base-content/70">
                                {{ $data['count'] }} {{ $data['count'] === 1 ? 'Stimme' : 'Stimmen' }}
                            </div>
                            <div class="text-xs mt-1 text-base-content/60">
                                {{ implode(', ', $data['participants']) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Voting Cards - At the bottom -->
            <div class="bg-base-200 rounded-xl shadow-md border border-base-300 p-6 sm:p-8 mb-6">
                <div class="text-center mb-6">
                    <div class="text-lg sm:text-xl font-semibold text-base-content mb-2">Wähle deine Schätzung</div>
                    <div class="text-sm text-base-content/70">Fibonacci Story Points</div>
                </div>

                @php
                    $hasVoted = $currentIssue && \App\Models\Vote::whereUserId(auth()->id())->whereIssueId($currentIssue->id)->exists();
                @endphp

                <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-9 gap-3 mb-5">
                    @foreach ($cards as $card)
                        @php
                            // Check if card is selected (either temporary selection or confirmed vote)
                            $isSelected = false;
                            if ($hasVoted) {
                                // User has voted - show confirmed vote
                                if ($vote !== null && $card !== '?' && $vote === $card) {
                                    $isSelected = true;
                                } elseif ($vote === null && $card === '?') {
                                    $isSelected = true;
                                }
                            } else {
                                // User hasn't voted - show temporary selection
                                $isSelected = ($selectedCard === $card);
                            }
                        @endphp
                        <button wire:click.prevent="selectCard({{ is_numeric($card) ? $card : "'{$card}'" }})" @class([
                            'aspect-[2/3] border-[3px] rounded-xl flex items-center justify-center text-2xl sm:text-2xl md:text-4xl font-bold cursor-pointer transition-all select-none',
                            'bg-secondary text-secondary-content border-base-300 hover:border-secondary hover:bg-base-300 hover:-translate-y-1 hover:shadow-lg hover:text-base-content' => ! $isSelected,
                            'bg-primary border-primary text-primary-content -translate-y-1 shadow-xl hover:text-base-content hover:bg-base-300' => $isSelected,
                            'opacity-50 cursor-not-allowed' => $hasVoted && ! $isSelected,
                        ]) wire:key="card-{{ $card }}" @if($hasVoted && ! $isSelected) disabled @endif>
                            {{ $card }}
                        </button>
                    @endforeach
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    @if($hasVoted)
                        @if(! $votesRevealed)
                            <button wire:click.prevent="removeVote" :class="{ 'opacity-50 cursor-not-allowed': $votesRevealed }"
                                class="flex-1 px-6 py-3.5 btn btn-error cursor-pointer font-semibold rounded-lg transition-colors w-full">
                                Löschen
                            </button>
                        @endif
                    @elseif($selectedCard !== null)
                        <button wire:click.prevent="confirmVote"
                            class="flex-1 px-6 py-3.5 btn btn-success cursor-pointer font-semibold rounded-lg transition-colors w-full">
                            Speichern
                        </button>
                    @endif
                    @if($currentIssue->status !== \App\Enums\IssueStatus::VOTING)
                        <button wire:click.prevent="clearSelection"
                            class="flex-1 px-6 py-3.5 btn btn-error cursor-pointer font-semibold rounded-lg transition-colors w-full">
                            Abbrechen
                        </button>
                    @endif
                </div>

            </div>
        @endif
    @endif
</div>