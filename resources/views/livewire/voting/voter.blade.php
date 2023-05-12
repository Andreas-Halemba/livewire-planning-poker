<div>
    <div class="grid gap-3 p-4 rounded-lg bg-base-300">
        <h2 class="order-first text-xl">New issues</h2>
        @foreach ($session->issues->where('status', '!=', 'finished') as $issue)
            <div
                tabindex="0"
                @class([
                    'border border-base-300 bg-base-100 rounded-box text-base-content',
                    'collapse-plus collapse' => !blank($issue->description),
                    '!border-accent order-first mb-4' => $issue->isVoting(),
                    '!border-neutral order-last' => $issue->isFinished(),
                    'border-yellow-500',
                ])
                wire:key="issue-{{ $issue->id }}"
            >
                <div class="text-xl font-medium collapse-title">
                    <div class="flex flex-col gap-1">
                        @if ($issue->isVoting())
                            <span class="badge badge-accent text-accent-content">Currently Voting</span>
                        @elseif ($issue->isFinished())
                            <span class="badge badge-info text-info-content">Done Voting</span>
                        @endif
                        <span>{!! $issue->title_html !!}</span>
                    </div>
                </div>
                @unless (blank($issue->description))
                    <div class="collapse-content">
                        <p></p>
                        <p>{{ $issue->description }}</p>
                    </div>
                @endunless
            </div>
        @endforeach
    </div>
    <div class="grid gap-3 p-4 mt-10 rounded-lg bg-base-300">
        <h2 class="text-xl">Done issues</h2>
        @foreach ($session->issues->where('status', 'finished') as $issue)
            <div
                tabindex="0"
                class="border bg-base-100 rounded-box text-base-content !border-neutral order-last"
                wire:key="issue-{{ $issue->id }}"
            >
                <div class="text-xl font-medium collapse-title">
                    <div class="flex flex-col gap-1">
                        <span class="badge badge-info text-info-content">{{ $issue->storypoints }}</span>
                        <span>{!! $issue->title_html !!}</span>
                    </div>
                </div>
                @unless (blank($issue->description))
                    <div class="collapse-content">
                        <p></p>
                        <p>{{ $issue->description }}</p>
                    </div>
                @endunless
            </div>
        @endforeach
    </div>
</div>
