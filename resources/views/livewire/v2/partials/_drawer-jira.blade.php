{{-- Jira Import UI --}}
@if(!$this->hasJiraCredentials())
    {{-- Keine Jira-Credentials --}}
    <div class="alert alert-warning mb-4">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h4 class="font-semibold">Jira nicht konfiguriert</h4>
            <p class="text-sm">Konfiguriere deine Jira-Zugangsdaten in den Profileinstellungen.</p>
        </div>
    </div>
    <a href="{{ route('profile.edit') }}" class="btn btn-primary w-full">
        Jetzt konfigurieren
    </a>
@else
    <div class="space-y-6">

        {{-- Error/Success Messages --}}
        @if($jiraError)
            <div class="alert alert-error text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $jiraError }}</span>
            </div>
        @endif

        @if($jiraSuccess)
            <div class="alert alert-success text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $jiraSuccess }}</span>
            </div>
        @endif

        {{-- Wenn noch keine Tickets geladen --}}
        @if(empty($jiraTickets))
            @include('livewire.v2.partials._drawer-jira-search')
        @else
            @include('livewire.v2.partials._drawer-jira-results')
        @endif
    </div>
@endif


