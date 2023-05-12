<div>
    <h1 class="mb-10 text-xl font-bold">Voting session</h1>
    <livewire:session-participants :session="$session" />
    @can('vote_session', $session)
        <livewire:voting-cards :session="$session"/>
    @endcan
    <div class="w-full">
        @can('owns_session', $session)
            @if ($session->issues->count() > 0)
                <livewire:voting.owner :session="$session" />
            @else
                <x-empty-session-warning
                    :session="$session"
                    :owner="true"
                />
            @endisset
        @elsecan('vote_session', $session)
            @if ($session->issues->count() > 0)
                <livewire:voting.voter :session="$session" />
            @else
                <x-empty-session-warning :session="$session" />
            @endisset
        @endcan
</div>
</div>
