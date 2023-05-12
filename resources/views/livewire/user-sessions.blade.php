<div class="w-full mb-10 shadow-xl card bg-base-300 text-base-content">
    <div class="card-body">
        <h2 class="card-title">Your voting Sessions</h2>
        <p>Here you can see all your sessions you took part in as voter.</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($sessions as $session)
                <div class="shadow-sm card card-compact bg-base-100 text-base-content">
                    <div class="card-body">
                        <h2 class="card-title">{{ $session->name }}</h2>
                        <p>{{ Illuminate\Support\Carbon::create($session->created_at)->toFormattedDateString() }}</p>
                        <div class="flex gap-2 card-actions">
                            <button
                                class="w-full btn btn-primary btn-sm"
                                wire:click.prevent="joinSession('{{ $session->invite_code }}')"
                            >Join</button>
                            <button
                                class="w-full btn btn-error btn-sm"
                                wire:click.prevent="deleteSession('{{ $session->id }}')"
                            >Leave</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
