<div class="w-full shadow-md bg-base-200 rounded-lg overflow-hidden">
    <div class="p-6">
        <h2 class="text-xl font-semibold text-base-content mb-2">Your upcoming Sessions</h2>
        <p class="text-base-content/70 mb-4">Here you can see all upcoming sessions you have created.</p>

        @if($sessions->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                No upcoming sessions available.
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
                            <tr class="hover" x-data="{ copied: false }">
                                <td class="font-medium text-base-content">{{ $session->name }}</td>
                                <td class="text-base-content/70">
                                    {{ Illuminate\Support\Carbon::create($session->created_at)->toFormattedDateString() }}
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-end">
                                        <button class="btn btn-outline btn-sm cursor-pointer"
                                            @click="navigator.clipboard.writeText('{{ route('session.voting', ['inviteCode' => $session->invite_code]) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                            x-text="copied ? 'Copied!' : 'Copy Link'">
                                            Copy Link
                                        </button>
                                        <a class="btn btn-primary btn-sm cursor-pointer"
                                            href="{{ route('session.voting', ['inviteCode' => $session->invite_code]) }}">
                                            Join
                                        </a>
                                        <button class="btn btn-warning btn-sm cursor-pointer"
                                            wire:click.prevent="archiveSession('{{ $session->id }}')">
                                            Archive
                                        </button>
                                        <button class="btn btn-error btn-sm cursor-pointer"
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
