<div class="w-full mb-10 shadow-xl card bg-base-300 text-base-content">
    <div class="card-body">
        <h2 class="card-title">Your own Sessions</h2>
        <p>Here you can see all your sessions you did create.</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($sessions as $session)
                <div class="shadow-sm card card-compact bg-base-100 text-base-content">
                    <div class="card-body" x-data>
                        <h2 class="card-title">{{ $session->name }}</h2>
                        <p>Created at: {{ Illuminate\Support\Carbon::create($session->created_at)->toFormattedDateString() }}</p>
                        <div class="flex gap-2 mt-2 card-actions">
                            <a
                            class="w-full btn btn-primary btn-sm btn-outline"
                            href="{{ route('session.voting', ['inviteCode' => $session->invite_code]) }}"
                            >Join</a>
                            <a
                                class="w-full btn btn-accent btn-sm btn-outline"
                                x-clipboard.raw="{{ route('session.voting', ['inviteCode' => $session->invite_code]) }}"
                            >Copy Invite link</a>
                            <a
                                class="w-full btn btn-error btn-sm btn-outline"
                                wire:click.prevent="deleteSession('{{ $session->id }}')"
                            >Delete</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>