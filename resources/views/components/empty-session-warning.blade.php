<div class="col-span-3 shadow-lg alert alert-error">
    <div>
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            class="flex-shrink-0 w-6 h-6 stroke-error-content"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
        </svg>
        <span>No issues have been added to the session.</span>
    </div>
    @can('owns_session', $session)
        <div class="flex-none">
            <a
                href="{{ $editSessionUrl }}"
                class="btn btn-primary btn-sm"
            >Add issues</a>
        </div>
    @endcan
</div>
