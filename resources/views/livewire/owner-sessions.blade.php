<div class="w-full shadow-md bg-base-200 rounded-lg overflow-hidden">
    <div class="p-6">
        <h2 class="text-xl font-semibold text-base-content mb-2">Your own Sessions</h2>
        <p class="text-base-content/70 mb-4">Here you can see all your sessions you did create.</p>
        
        @if($sessions->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                No sessions created yet.
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
                            <tr class="hover" x-data>
                                <td class="font-medium text-base-content">{{ $session->name }}</td>
                                <td class="text-base-content/70">
                                    {{ Illuminate\Support\Carbon::create($session->created_at)->toFormattedDateString() }}
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-end">
                                        <a class="btn btn-primary btn-sm"
                                            href="{{ route('session.voting', ['inviteCode' => $session->invite_code]) }}">
                                            Join
                                        </a>
                                        <button class="btn btn-accent btn-sm"
                                            x-clipboard.raw="{{ route('session.voting', ['inviteCode' => $session->invite_code]) }}">
                                            Copy Link
                                        </button>
                                        <button class="btn btn-error btn-sm"
                                            wire:click.prevent="deleteSession('{{ $session->id }}')">
                                            Delete
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
