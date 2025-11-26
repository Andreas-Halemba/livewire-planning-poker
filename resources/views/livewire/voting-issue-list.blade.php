<div>
    <h3 class="mb-4 text-xl font-bold">Issues</h3>
    <div class="flex p-4 bg-slate-300">
        <div class="w-9/12">Issue</div>
        <div class="w-3/12 text-center">Storypoints</div>
    </div>
    <div class="flex flex-col">
        @foreach ($issues as $issue)
            <div x-data="{ open: false }" @click="open ^= 1" @click.away="open = false"
                class="flex flex-wrap p-4 cursor-pointer even:bg-slate-100">
                <div class="w-9/12">{!! $issue->TitleHtml !!}</div>
                <div class="w-3/12 text-center">{{ $issue->averageVote ?? 'X' }}</div>
                @if ($issue->description)
                    <div class="w-full py-3" x-show="open">
                        <div class="prose prose-sm max-w-none">
                            {!! $issue->description !!}
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>