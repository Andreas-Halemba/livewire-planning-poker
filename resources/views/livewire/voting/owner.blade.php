<div>
    <div class="grid grid-cols-1 gap-3 p-4 mb-10 rounded-lg md:grid-cols-2 lg:grid-cols-3 bg-base-300">
        <h2 class="col-span-full">Open Issues</h2>
        @foreach ($issues->where('status', '!=', Issue::STATUS_FINISHED) as $index => $issue)
            <div
                @class([
                    'box-border shadow-xl card bg-base-100 card-compact',
                    'bg-primary text-primary-content' => $issue->isVoting(),
                ])
                wire:key="issue-{{ $issue->id }}"
            >
                <div class="justify-between card-body">
                    <div class="card-title">{!! $issue->title_html !!}</div>
                    @if (!blank($issue->description))
                        <p>{{ $issue->description }}</p>
                    @endif
                    @if ($issue->status === 'voting')
                        <p>Enter storypoints and save them</p>
                    @endif
                    <div class="justify-stretch card-actions">
                        @if ($issue->status === 'voting')
                            <div class="form-control">
                                <form
                                    class="input-group"
                                    wire:submit.prevent="addPointsToIssue({{ $issue->id }})"
                                >
                                    <input
                                        type="text"
                                        wire:model.defer="issues.{{ $index }}.storypoints"
                                        placeholder="Points"
                                        class="w-24 input text-neutral"
                                    >
                                    <button
                                        type="submit"
                                        class="btn grow btn-success"
                                    >Save</button>
                                    <button
                                        wire:click.prevent="cancelIssue({{ $issue->id }})"
                                        class="btn grow btn-error"
                                    >Cancel</button>
                                </form>
                            </div>
                        @elseif ($issue->status === Issue::STATUS_NEW)
                            <button
                                wire:click.prevent="voteIssue({{ $issue->id }})"
                                class="btn btn-primary btn-sm"
                            >Vote now</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        <div class="box-border order-last shadow-xl card bg-base-100 card-compact">
            <div class="justify-between card-body">
                <form wire:submit.prevent="addIssue()">
                    <div class="card-title">Add new issue</div>
                    <div class="gap-3 mt-3 form-control">
                        <input
                            required
                            type="text"
                            name="titleTitle"
                            wire:model="issueTitle"
                            placeholder="Title"
                            class="input input-sm input-bordered"
                        />
                        @error('titleTitle')
                            <span class="text-error">{{ $message }}</span>
                        @enderror
                        <textarea
                            type="textarea"
                            wire:model="issueDescription"
                            naem="issueDescription"
                            placeholder="Description"
                            class="textarea textarea-sm textarea-bordered"
                        ></textarea>
                        @error('issueDescription')
                            <span class="text-error">{{ $message }}</span>
                        @enderror
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                        >Add new issue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-3 p-4 rounded-lg md:grid-cols-2 lg:grid-cols-3 bg-base-300">
        <h2 class="col-span-full">Estimated Issues</h2>
        @foreach ($issues->where('status', Issue::STATUS_FINISHED) as $index => $issue)
            <div
                class="box-border shadow-xl card bg-base-100 card-compact card-bordered border-accent"
                wire:key="issue-{{ $issue->id }}"
            >
                <div class="justify-between card-body">
                    <div class="justify-end card-actions">
                        <div
                            class="absolute inline-flex items-center justify-center w-8 h-8 text-xl rounded-box bg-accent text-accent-content">
                            {{ $issue->storypoints ?? 'X' }}</div>
                    </div>
                    <div class="card-title">{!! $issue->title_html !!}</div>
                    @if (!blank($issue->description))
                        <p>{{ $issue->description }}</p>
                    @endif
                    <div class="justify-end card-actions">
                        <div
                            wire:click.prevent="voteIssue({{ $issue->id }})"
                            class="btn btn-primary btn-sm"
                        >Vote again</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
