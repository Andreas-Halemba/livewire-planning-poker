@if($currentIssue)
    <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8 mb-6">
        <div class="text-center mb-6">
            <div class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Wähle deine Schätzung</div>
            <div class="text-sm text-gray-600">Fibonacci Story Points</div>
        </div>

        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-9 gap-3 mb-5">
            @foreach ($cards as $card)
                @php
                    $isSelected = ($card === '?' && $vote === null) || ($card !== '?' && $vote === $card);
                    $cardValue = $card === '?' ? "'?'" : $card;
                @endphp
                <button wire:click.prevent="voteIssue({{ $cardValue }})" @class([
                    'aspect-[2/3] bg-white border-[3px] rounded-xl flex items-center justify-center text-2xl sm:text-3xl md:text-4xl font-bold cursor-pointer transition-all select-none',
                    'border-gray-300 hover:border-indigo-500 hover:bg-indigo-50 hover:-translate-y-1 hover:shadow-lg text-gray-900' => !$isSelected,
                    'border-indigo-500 bg-indigo-500 text-white -translate-y-1 shadow-xl' => $isSelected,
                ]) wire:key="card-{{ $card }}">
                    {{ $card }}
                </button>
            @endforeach
        </div>

        @php
            $hasVoted = $currentIssue && \App\Models\Vote::whereUserId(auth()->id())->whereIssueId($currentIssue->id)->exists();
        @endphp
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
            @else
                <div
                    class="flex-1 px-6 py-3.5 bg-gray-300 text-gray-500 font-semibold rounded-lg cursor-not-allowed flex items-center justify-center">
                    Wähle eine Karte aus
                </div>
            @endif
        </div>
    </div>
@endif
