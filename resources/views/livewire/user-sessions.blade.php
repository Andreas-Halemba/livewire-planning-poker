<div class="w-full shadow-md bg-base-200 rounded-lg overflow-hidden">
    <div class="p-6">
        <h2 class="text-xl font-semibold text-base-content mb-2">Your voting Sessions</h2>
        <p class="text-base-content/70 mb-4">Here you can see all your sessions you took part in as voter.</p>
        
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
                                        <button class="btn btn-primary btn-sm"
                                            wire:click.prevent="joinSession('{{ $session->invite_code }}')">
                                            Join
                                        </button>
                                        <button class="btn btn-error btn-sm"
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
