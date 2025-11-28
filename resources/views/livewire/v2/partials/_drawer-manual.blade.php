{{-- Manuelles Issue-Formular --}}
<form wire:submit="addIssue">
    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-medium">Titel *</span>
        </label>
        <input type="text"
               wire:model="newIssueTitle"
               placeholder="Issue-Titel eingeben..."
               class="input input-bordered w-full"
               required />
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
                  class="textarea textarea-bordered w-full h-24"
        ></textarea>
    </div>

    <div class="divider text-xs">Jira (optional)</div>

    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-medium">Issue Key</span>
        </label>
        <input type="text"
               wire:model="newIssueJiraKey"
               placeholder="z.B. SAN-1234"
               class="input input-bordered w-full" />
    </div>

    <div class="form-control mb-6">
        <label class="label">
            <span class="label-text font-medium">Jira URL</span>
        </label>
        <input type="url"
               wire:model="newIssueJiraUrl"
               placeholder="https://jira.example.com/browse/SAN-1234"
               class="input input-bordered w-full" />
    </div>

    <button type="submit" class="btn btn-primary w-full">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Issue hinzufügen
    </button>
</form>


