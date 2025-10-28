<div>
    <div class="grid grid-cols-1 gap-3 p-4 mb-10 rounded-lg md:grid-cols-2 lg:grid-cols-3 bg-base-300">
        <h2 class="text-lg font-bold col-span-full">Open Issues</h2>
        @foreach ($issues->where('status', '!=', Issue::STATUS_FINISHED) as $index => $issue)
            <div @class([
                'box-border shadow-xl card bg-base-100 card-compact',
                'bg-primary text-primary-content' => $issue->isVoting(),
            ]) wire:key="issue-{{ $issue->id }}">
                <div class="relative justify-between card-body">
                    <div class="absolute justify-end top-4 right-4 card-actions">
                        <x-danger-button class="btn-sm btn-outline btn-square"
                            wire:click="deleteIssue({{ $issue->id }})">X</x-danger-button>
                    </div>
                    <div class="card-title">{!! $issue->title_html !!}</div>
                    @if (!blank($issue->description))
                        <p>Description: <br> {{ $issue->description }}</p>
                    @else
                        <p>No description</p>
                    @endif
                    <div class="flex flex-col gap-2">
                        @if ($issue->status === 'voting')
                            <p>Enter storypoints and save them</p>
                        @endif
                        <div class="justify-stretch card-actions">
                            @if ($issue->status === 'voting')
                                <div class="w-full form-control">
                                    <form class="input-group" wire:submit="addPointsToIssue({{ $issue->id }})">
                                        <x-text-input class="w-1/3 text-center input-sm" name="storypoints" placeholder="Points"
                                            wire:model="issues.{{ $index }}.storypoints" />
                                        <x-success-button class=" grow btn-sm btn">Save</x-success-button>
                                        <x-danger-button class="grow btn-sm"
                                            wire:click.prevent="cancelIssue({{ $issue->id }})">Cancel</x-danger-button>

                                    </form>
                                </div>
                            @elseif ($issue->status === Issue::STATUS_NEW)
                                <x-primary-button wire:click.prevent="voteIssue({{ $issue->id }})" class="w-full btn-sm">Vote
                                    now</x-primary-button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="box-border order-last shadow-xl md:w-1/2 lg:w-1/3 card bg-base-100 card-compact col-span-full">
            <div class="justify-between card-body">
                <livewire:jira-import :session="$session" />
            </div>
        </div>
        <div class="box-border order-last shadow-xl md:w-1/2 lg:w-1/3 card bg-base-100 card-compact col-span-full">
            <div class="justify-between card-body">
                <form wire:submit="addIssue()">
                    <div class="card-title">Add new issue</div>
                    <div class="gap-3 mt-3 form-control">
                        <x-text-input required class="input-md" wire:model.live="issueTitle" placeholder="Title" />
                        @error('titleTitle')
                            <span class="text-error">{{ $message }}</span>
                        @enderror
                        <x-textarea-input wire:model.live="issueDescription"
                            placeholder="Description"></x-textarea-input>
                        @error('issueDescription')
                            <span class="text-error">{{ $message }}</span>
                        @enderror
                        <button type="submit" class="btn btn-primary btn-sm btn-outline">Add new issue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-3 p-4 rounded-lg md:grid-cols-2 lg:grid-cols-3 bg-base-300">
        <h2 class="text-lg font-bold col-span-full">Estimated Issues</h2>
        @foreach ($issues->where('status', Issue::STATUS_FINISHED) as $index => $issue)
            <div class="box-border shadow-xl card bg-base-100 card-compact card-bordered border-accent"
                wire:key="issue-{{ $issue->id }}">
                <div class="justify-between card-body">
                    <div class="justify-end card-actions">
                        <div
                            class="absolute inline-flex items-center justify-center w-8 h-8 text-xl border rounded-box border-accent text-accent-content">
                            {{ $issue->storypoints ?? 'X' }}
                        </div>
                    </div>
                    <div class="card-title">{!! $issue->title_html !!}</div>
                    @if (!blank($issue->description))
                        <p>{{ $issue->description }}</p>
                    @endif
                    <div class="justify-end card-actions">
                        <div wire:click.prevent="voteIssue({{ $issue->id }})" class="btn btn-primary btn-sm btn-outline">
                            Vote again</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
