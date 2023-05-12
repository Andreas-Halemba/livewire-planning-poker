<div class="grid gap-3 p-4 rounded-lg bg-base-300">
    @foreach ($session->issues as $issue)
    <div
    tabindex="0"
    @class([
        'border border-base-300 bg-base-100 rounded-box text-base-content',
        'collapse-plus collapse' => !blank($issue->description),
        'collapse-open !border-accent order-first mb-4' => $issue->isVoting(),
        '!border-neutral order-last' => $issue->isFinished(),
        'border-info',
        ])
        >
        <div class="text-xl font-medium collapse-title">
                @if($issue->isVoting())
                    <p class="text-accent">Currently voting</p>
                @endif
                {!! $issue->title_html !!} <br><span class="text-sm">{{ $issue->created_at }}</span>
            </div>
            @unless(blank($issue->description))
                <div class="collapse-content">
                    <p></p>
                    <p>{{ $issue->description }}</p>
                </div>
            @endunless
        </div>
    @endforeach
</div>
