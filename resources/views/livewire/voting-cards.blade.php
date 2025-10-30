<div>
    @if($currentIssue)
        <!-- Issue Card - Show when manually selected (not STATUS_VOTING) -->
        @if($currentIssue->status !== \App\Models\Issue::STATUS_VOTING)
            <div class="bg-base-100 rounded-xl shadow-md p-6 sm:p-8 mb-6 border-2 border-accent"
                x-data="{ descriptionOpen: false }">
                <div class="text-xs font-semibold text-accent uppercase tracking-wide mb-3">Asynchron schätzen</div>
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
        @endif

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
                        'bg-secondary border-base-300 hover:border-primary hover:bg-secondary/10 hover:-translate-y-1 hover:shadow-lg text-secondary-content' => !$isSelected,
                        'bg-primary border-primary text-primary-content -translate-y-1 shadow-xl' => $isSelected,
                        'opacity-50 cursor-not-allowed' => $hasVoted && !$isSelected,
                    ]) wire:key="card-{{ $card }}" @if($hasVoted && !$isSelected) disabled @endif>
                        {{ $card }}
                    </button>
                @endforeach
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if($hasVoted)
                    <button wire:click.prevent="removeVote"
                        class="flex-1 px-6 cursor-pointer py-3.5 bg-warning hover:bg-warning/90 text-warning-content font-semibold rounded-lg transition-colors w-full">
                        Schätzung löschen
                    </button>
                @elseif($selectedCard !== null)
                    <button wire:click.prevent="confirmVote"
                        class="flex-1 px-6 py-3.5 bg-success hover:bg-success/90 text-success-content font-semibold rounded-lg transition-colors w-full">
                        Schätzung speichern
                    </button>
                @endif
            </div>

            @if($currentIssue->status !== \App\Models\Issue::STATUS_VOTING)
                <button wire:click.prevent="clearSelection"
                    class="mt-4 w-full px-6 py-3 border border-warning bg-warning/15 text-warning font-semibold rounded-lg uppercase tracking-wide hover:bg-warning/25 hover:text-warning/90 transition-colors cursor-pointer">
                    Schätzung abbrechen
                </button>
            @endif
        </div>
    @endif
</div>
