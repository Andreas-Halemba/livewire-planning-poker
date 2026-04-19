{{-- Manuelles Issue-Formular --}}
<form wire:submit.prevent="addIssue" novalidate>
    @if ($errors->any())
        <div class="alert alert-error mb-4 text-sm" role="alert">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-medium">Titel *</span>
        </label>
        <input type="text"
               wire:model="newIssueTitle"
               placeholder="Issue-Titel eingeben..."
               class="input input-bordered w-full @error('newIssueTitle') input-error @enderror" />
        @error('newIssueTitle')
            <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
            </label>
        @enderror
    </div>

    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-medium">Beschreibung</span>
        </label>
        <textarea wire:model="newIssueDescription"
                  placeholder="Optionale Beschreibung..."
                  class="textarea textarea-bordered h-24 w-full @error('newIssueDescription') textarea-error @enderror"
        ></textarea>
        @error('newIssueDescription')
            <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
            </label>
        @enderror
    </div>

    <div class="divider text-xs">Jira (optional)</div>

    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-medium">Issue Key</span>
        </label>
        <input type="text"
               wire:model="newIssueJiraKey"
               placeholder="z.B. SAN-1234"
               class="input input-bordered w-full @error('newIssueJiraKey') input-error @enderror" />
        @error('newIssueJiraKey')
            <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
            </label>
        @enderror
    </div>

    <div class="form-control mb-6">
        <label class="label">
            <span class="label-text font-medium">Jira URL</span>
        </label>
        <input type="text"
               inputmode="url"
               autocomplete="url"
               wire:model="newIssueJiraUrl"
               placeholder="https://jira.example.com/browse/SAN-1234"
               class="input input-bordered w-full @error('newIssueJiraUrl') input-error @enderror" />
        @error('newIssueJiraUrl')
            <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
            </label>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-full">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Issue hinzufügen
    </button>
</form>


