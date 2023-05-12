<div>
    @if ($currentIssue)
        <div
            class="grid w-full grid-cols-2 gap-5 p-4 mb-10 text-center sm:grid-cols-3 md:grid-cols-5 bg-base-300 rounded-box">
    
            <h2 class="text-lg col-span-full">Pick a Card for <span class="text-primary">{!! $currentIssue->title_html !!}</span></h2>
            @foreach ($cards as $card)
                <div
                    wire:click.prevent="voteIssue({{ $card }})"
                    class="h-24 text-5xl justify-self-center cursor-pointer badge badge-ghost aspect-square hover:badge-info hover:text-success-content @if ($vote === $card) !badge-info !text-success-content @endif)"
                >
                    {{ $card }}
                </div>
            @endforeach
        </div>
    @endif
</div>
