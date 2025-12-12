<div class="w-full shadow-md bg-base-200 rounded-lg overflow-hidden">
    <div class="p-6">
        <h2 class="text-xl font-semibold text-base-content mb-2">Your voting Sessions</h2>
        <p class="text-base-content/70 mb-4">
            Here you can see all sessions you joined as a voter.
            <span class="text-base-content/60">Use “Async” for pre-estimates or “Live” for the normal voting session.</span>
        </p>
        
        @if($sessions->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                No voting sessions available.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-base-content">Session Name</th>
                            <th class="text-base-content">Created</th>
                            <th class="text-base-content text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            <tr class="hover">
                                <td class="font-medium text-base-content">{{ $session->name }}</td>
                                <td class="text-base-content/70">
                                    {{ Illuminate\Support\Carbon::create($session->created_at)->toFormattedDateString() }}
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-end">
                                        <a class="btn btn-accent btn-sm cursor-pointer tooltip tooltip-left"
                                            data-tip="Async pre-estimates (no live round)"
                                            href="{{ route('session.async', ['inviteCode' => $session->invite_code]) }}">
                                            Async
                                        </a>
                                        <button class="btn btn-primary btn-sm cursor-pointer tooltip tooltip-left"
                                            data-tip="Open live session (voting)"
                                            wire:click.prevent="joinSession('{{ $session->invite_code }}')">
                                            Live
                                        </button>
                                        <button class="btn btn-error btn-sm cursor-pointer tooltip tooltip-left"
                                            data-tip="Leave session (you will be removed)"
                                            wire:confirm="Leave this session? You will be removed as a participant."
                                            wire:click.prevent="leaveSession('{{ $session->id }}')">
                                            Leave
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
