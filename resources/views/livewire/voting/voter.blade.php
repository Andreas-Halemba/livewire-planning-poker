<div>
    @php
        $upcomingIssues = $session->issues->where('status', '!=', 'finished')->where('status', '!=', 'voting');
        $estimatedIssues = $session->issues->where('status', 'finished');
    @endphp

    <!-- Upcoming Issues (Collapsible) -->
    @if($upcomingIssues->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-5 sm:p-6 mb-4" x-data="{ open: false }">
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between text-base font-semibold text-gray-900 cursor-pointer hover:text-indigo-600 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Noch zu schätzen
                    <span class="text-gray-600 font-normal text-sm">({{ $upcomingIssues->count() }} Issues)</span>
                </div>
                <svg
                    class="w-5 h-5 text-gray-600 transition-transform"
                    :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="open"
                x-collapse
                class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex flex-col gap-2">
                    @foreach($upcomingIssues as $issue)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-semibold text-gray-600">
                                    {{ $issue->jira_key ?? 'Issue' }}
                                </div>
                                <div class="text-sm sm:text-base text-gray-900 mt-1 truncate">
                                    {{ $issue->title }}
                                </div>
                            </div>
                            @if($loop->first)
                                <span class="text-xs font-semibold px-2 py-1 bg-yellow-100 text-yellow-800 rounded uppercase ml-2 flex-shrink-0">
                                    Nächstes
                                </span>
                            @else
                                <span class="text-xs font-semibold px-2 py-1 bg-gray-100 text-gray-600 rounded uppercase ml-2 flex-shrink-0">
                                    Wartend
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Estimated Issues History (Collapsible) -->
    @if($estimatedIssues->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-5 sm:p-6" x-data="{ open: false }">
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between text-base font-semibold text-gray-900 cursor-pointer hover:text-indigo-600 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Bereits geschätzt
                    <span class="text-gray-600 font-normal text-sm">({{ $estimatedIssues->count() }} Issues)</span>
                </div>
                <svg
                    class="w-5 h-5 text-gray-600 transition-transform"
                    :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="open"
                x-collapse
                class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex flex-col gap-2">
                    @foreach($estimatedIssues as $issue)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-semibold text-gray-600">
                                    {{ $issue->jira_key ?? 'Issue' }}
                                </div>
                                <div class="text-sm sm:text-base text-gray-900 mt-1 truncate">
                                    {{ $issue->title }}
                                </div>
                            </div>
                            <div class="text-base sm:text-lg font-semibold text-green-600 ml-2 flex-shrink-0">
                                {{ $issue->storypoints }} SP
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
