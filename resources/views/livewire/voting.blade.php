<div>
    <h1 class="mb-10 text-xl font-bold">Session: <b>{{ $session->name }}</b></h1>
    <livewire:session-participants :session="$session" />
    @can('vote_session', $session)
        <livewire:voting-cards :session="$session" />
    @endcan
    <div class="w-full">
        @can('owns_session', $session)
            <livewire:voting.owner :session="$session" />
        @elsecan('vote_session', $session)
            @if ($session->issues->count() > 0)
                <livewire:voting.voter :session="$session" />
            @else
                <x-empty-session-warning :session="$session" />
            @endisset
        @endcan
</div>
</div>
