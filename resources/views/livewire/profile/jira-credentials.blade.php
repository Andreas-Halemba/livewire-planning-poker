<section class="rounded-box">
    <header>
        <h2 class="text-lg font-medium text-base-content">
            {{ __('Jira Credentials') }}
        </h2>

        <p class="mt-1 text-sm text-base-content">
            {{ __('Configure your Jira credentials to import issues from your Jira instance.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <!-- Info Box -->
        <div class="p-4 rounded-lg bg-base-200 border border-base-300">
            <h3 class="font-semibold text-base-content">How to get your API Token</h3>
            <ol class="mt-2 ml-4 list-decimal space-y-1 text-sm text-base-content">
                <li>Go to <a href="https://id.atlassian.com/manage-profile/security/api-tokens" target="_blank"
                        class="text-primary underline">Atlassian Account Settings</a></li>
                <li>Click on "API tokens"</li>
                <li>Click "Create API token"</li>
                <li>Give it a label (e.g., "Planning Poker")</li>
                <li>Copy the generated token</li>
                <li>Paste it in the field below</li>
            </ol>
        </div>

        <!-- Jira URL -->
        <div class="flex flex-col gap-2">
            <x-input-label for="jira_url" :value="__('Jira URL')" class="text-base-content" />
            <x-text-input wire:model="jira_url" class="bg-white" id="jira_url" type="url"
                placeholder="https://yourcompany.atlassian.net" />
            <x-input-error :messages="$errors->get('jira_url')" />
        </div>

        <!-- Jira User -->
        <div class="flex flex-col gap-2">
            <x-input-label for="jira_user" :value="__('Jira Email')" class="text-base-content" />
            <x-text-input wire:model="jira_user" class="bg-white" id="jira_user" type="email"
                placeholder="your-email@company.com" />
            <x-input-error :messages="$errors->get('jira_user')" />
        </div>

        <!-- Jira API Key -->
        <div class="flex flex-col gap-2">
            <x-input-label for="jira_api_key" :value="__('Jira API Token')" class="text-base-content" />
            <x-text-input wire:model="jira_api_key" class="bg-white" id="jira_api_key" type="password"
                placeholder="Enter API token" />
            <p class="text-xs text-base-content/70">
                {{ __('Stars indicate an existing API token. Leave as is to keep your current token, or enter a new one to change it.') }}
            </p>
            <x-input-error :messages="$errors->get('jira_api_key')" />
        </div>

        <!-- Connection Test Result -->
        @if ($connectionTested)
            <div
                class="p-4 rounded-lg {{ $connectionSuccessful ? 'bg-base-200 border border-success' : 'bg-base-200 border border-error' }}">
                <p class="text-sm {{ $connectionSuccessful ? 'text-success font-semibold' : 'text-error font-semibold' }}">
                    {{ $connectionMessage }}
                </p>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <x-primary-button wire:click="testConnection" wire:loading.attr="disabled">
                {{ __('Test Connection') }}
            </x-primary-button>

            <x-primary-button wire:click="save" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-primary-button>
        </div>

        @if (session('status') === 'jira-credentials-updated')
            <p class="text-sm text-success">{{ __('Credentials saved successfully.') }}</p>
        @endif
    </div>
</section>
