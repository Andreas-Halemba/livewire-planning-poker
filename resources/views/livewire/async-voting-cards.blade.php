<div class="card bg-base-200 shadow-lg border border-base-300">
    <div class="card-body p-5 sm:p-6">
        <h2 class="card-title text-base sm:text-lg">
            <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Async schätzen
        </h2>

        @if(!$selectedIssue)
            <div class="text-sm text-base-content/70 mt-2">
                Wähle unten ein Issue aus, um deine Vorab-Schätzung abzugeben.
            </div>
        @else
            <div class="mt-3">
                @if($selectedIssue->jira_url || $selectedIssue->jira_key)
                    <a href="{{ $selectedIssue->getJiraBrowserUrl() }}"
                        target="_blank"
                        rel="nofollow"
                        class="inline-flex items-center gap-1 text-xs text-info hover:underline mb-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.571 11.513H0a5.218 5.218 0 0 0 5.232 5.215h2.13v2.057A5.215 5.215 0 0 0 12.575 24V12.518a1.005 1.005 0 0 0-1.005-1.005zm5.723-5.756H5.736a5.215 5.215 0 0 0 5.215 5.214h2.129v2.058a5.218 5.218 0 0 0 5.215 5.214V6.758a1.001 1.001 0 0 0-1.001-1.001zM23.013 0H11.455a5.215 5.215 0 0 0 5.215 5.215h2.129v2.057A5.215 5.215 0 0 0 24 12.483V1.005A1.005 1.005 0 0 0 23.013 0z"/>
                        </svg>
                        {{ $selectedIssue->jira_key ?? 'Jira öffnen' }}
                    </a>
                @endif

                <div class="font-semibold text-base-content break-words">
                    {{ $selectedIssue->title }}
                </div>

                @if($selectedIssue->description)
                    <div x-data="{ expanded: false }" class="mt-2">
                        <div class="relative">
                            <div class="text-base-content text-sm prose prose-sm max-w-none jira-description"
                                 :class="expanded ? '' : 'max-h-16 overflow-hidden'">
                                {!! $selectedIssue->formatted_description !!}
                            </div>
                            <div x-show="!expanded"
                                 class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-base-200 to-transparent pointer-events-none">
                            </div>
                        </div>
                        <button @click="expanded = !expanded" class="btn btn-sm btn-ghost mt-2 gap-1">
                            <span x-text="expanded ? 'Weniger anzeigen' : 'Mehr lesen'"></span>
                            <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t border-base-content/10">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="text-sm font-semibold">Deine Schätzung</div>
                        @if($myVote !== null)
                            <span class="badge badge-success badge-outline">
                                Gespeichert
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
                        @foreach($cards as $card)
                            @php
                                $isSelected = $selectedCard === $card;
                                $isMyVote = $myVote === $card;
                            @endphp
                            <button
                                wire:key="async-card-{{ $card }}"
                                wire:click="chooseCard({{ $card }})"
                                @class([
                                    'btn btn-lg font-bold',
                                    'btn-primary' => $isSelected,
                                    'btn-ghost bg-base-content/10 hover:bg-base-content/20' => ! $isSelected,
                                    'ring-2 ring-success/40' => $isMyVote && ! $isSelected,
                                ])>
                                {{ $card }}
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        <button wire:click="clearSelection" class="btn btn-error">
                            Abbrechen
                        </button>
                        <div class="flex-1"></div>
                        @if($myVote !== null)
                            <button wire:click="removeVote" class="btn btn-warning">
                                Vote entfernen
                            </button>
                        @endif
                        <button wire:click="saveVote" @disabled($selectedCard === null) class="btn btn-success">
                            Speichern
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>


