<div>
    <div class="grid gap-3 p-4 rounded-lg bg-base-300">
        <h2 class="order-first text-xl">Issues</h2>
        @foreach ($session->issues->where('status', '!=', 'finished') as $issue)
            <div
                tabindex="0"
                @class([
                    'border border-base-300 bg-base-100 rounded-box text-base-content',
                    'collapse-plus collapse' => !blank($issue->description),
                    '!border-success order-first mb-10 !collapse-open' => $issue->isVoting(),
                    '!border-neutral order-last' => $issue->isFinished(),
                    'border-yellow-500',
                ])
                wire:key="issue-{{ $issue->id }}"
            >
                <div class="text-xl font-medium collapse-title">
                    <div class="flex flex-col gap-1">
                        @if ($issue->isVoting())
                            <span class="badge badge-success text-success-content">Currently Voting</span>
                        @elseif ($issue->isFinished())
                            <span class="badge badge-info text-info-content">Done Voting</span>
                        @endif
                        <span>{!! $issue->title_html !!}</span>
                    </div>
                </div>
                @unless (blank($issue->description))
                    <div class="collapse-content">
                        <div class="prose prose-sm max-w-none">
                            {!! $this->formatJiraDescription($issue->description) !!}
                        </div>
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
                @class([
                    'border order-last bg-base-100 text-base-content !border-neutral rounded-box',
                    'collapse-plus collapse' => !blank($issue->description),
                ])
                wire:key="issue-{{ $issue->id }}"
            >
                <div class="text-xl font-medium collapse-title">
                    <div class="flex items-center gap-4">
                        <span class="w-10 h-5 badge badge-success">{{ $issue->storypoints }}</span>
                        <span >{!! $issue->title_html !!}</span>
                    </div>
                </div>
                @unless (blank($issue->description))
                    <div class="collapse-content">
                        <div class="prose prose-sm max-w-none">
                            {!! $this->formatJiraDescription($issue->description) !!}
                        </div>
                    </div>
                @endunless
            </div>
        @endforeach
    </div>
</div>
