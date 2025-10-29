<div>
    @if($currentIssue)
        <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8 mb-6">
            <div class="text-center mb-6">
                <div class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Wähle deine Schätzung</div>
                <div class="text-sm text-gray-600">Fibonacci Story Points</div>
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
                        'aspect-[2/3] border-[3px] rounded-xl flex items-center justify-center text-2xl sm:text-3xl md:text-4xl font-bold cursor-pointer transition-all select-none',
                        'bg-white border-gray-300 hover:border-primary hover:bg-primary/10 hover:-translate-y-1 hover:shadow-lg text-gray-900' => !$isSelected,
                        'bg-primary border-primary text-primary-content -translate-y-1 shadow-xl' => $isSelected,
                        'opacity-50 cursor-not-allowed' => $hasVoted && !$isSelected,
                    ]) wire:key="card-{{ $card }}"
                        @if($hasVoted && !$isSelected) disabled @endif>
                        {{ $card }}
                    </button>
                @endforeach
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if($hasVoted)
                    <button wire:click.prevent="removeVote"
                        class="flex-1 px-6 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                        Schätzung ändern
                    </button>
                    <div
                        class="flex-1 px-6 py-3.5 bg-green-500 text-white font-semibold rounded-lg flex items-center justify-center">
                        ✓ Schätzung abgegeben
                        @if($vote !== null)
                            ({{ $vote }} SP)
                        @else
                            (?)
                        @endif
                    </div>
                @elseif($selectedCard !== null)
                    <button wire:click.prevent="confirmVote"
                        class="flex-1 px-6 py-3.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Schätzung abgeben
                    </button>
                @else
                    <div
                        class="flex-1 px-6 py-3.5 bg-gray-300 text-gray-500 font-semibold rounded-lg cursor-not-allowed flex items-center justify-center">
                        Wähle eine Karte aus
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
