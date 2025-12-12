<div class="w-full shadow-lg bg-base-200 rounded-xl overflow-hidden border border-base-300">
    <div class="p-5 sm:p-6">
        <h2 class="text-xl font-semibold text-base-content mb-2">Archived Sessions</h2>
        <p class="text-base-content/70 mb-4">Previously archived voting sessions appear here for reference.</p>

        @if ($sessions->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                No archived sessions yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-base-content">Session Name</th>
                            <th class="text-base-content">Archived</th>
                            <th class="text-base-content text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            <tr class="hover">
                                <td class="font-medium text-base-content">{{ $session->name }}</td>
                                <td class="text-base-content/70">
                                    {{ optional($session->archived_at)->toFormattedDateString() }}
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-end">
                                        <a class="btn btn-outline btn-sm cursor-pointer"
                                            href="{{ route('session.archived', ['inviteCode' => $session->invite_code]) }}">
                                            View
                                        </a>
                                        <button class="btn btn-success btn-sm cursor-pointer"
                                            wire:click.prevent="unarchiveSession('{{ $session->id }}')">
                                            Reactivate
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
