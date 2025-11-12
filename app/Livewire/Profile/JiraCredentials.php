<?php

namespace App\Livewire\Profile;

use App\Services\JiraService;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class JiraCredentials extends Component
{
    use InspectorLivewire;

    public string $jira_url = '';
    public string $jira_user = '';
    public string $jira_api_key = '';
    public bool $connectionTested = false;
    public bool $connectionSuccessful = false;
    public string $connectionMessage = '';

    /** @var array<string, string> */
    protected array $rules = [
        'jira_url' => 'nullable|url|max:255',
        'jira_user' => 'nullable|email|max:255',
        'jira_api_key' => 'nullable|string|max:255',
    ];

    /** @var array<string, string> */
    protected array $messages = [
        'jira_url.url' => 'The Jira URL must be a valid URL.',
        'jira_user.email' => 'The Jira user must be a valid email address.',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $this->jira_url = $user->jira_url ?? '';
        $this->jira_user = $user->jira_user ?? '';

        // Set a placeholder value to indicate that an API key exists
        // The actual value will remain unchanged if user doesn't modify it
        if ($user->jira_api_key) {
            $length = strlen($user->jira_api_key);
            $this->jira_api_key = str_repeat('•', $length); // Set placeholder stars matching actual length
        }
    }

    public function render()
    {
        return view('livewire.profile.jira-credentials');
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();
        $user->jira_url = $this->jira_url;
        $user->jira_user = $this->jira_user;

        // Only update API key if a new one was provided and it's different from the placeholder
        if (!empty($this->jira_api_key)) {
            // Check if the user entered the placeholder stars
            $currentApiKey = $user->jira_api_key ?? '';
            $length = strlen($currentApiKey);
            $placeholderStars = str_repeat('•', $length);

            // Only update if the entered value is different from the placeholder
            if ($this->jira_api_key !== $placeholderStars) {
                $user->jira_api_key = $this->jira_api_key;
            }
        }

        $user->save();

        $this->dispatch('profile-updated');

        session()->flash('status', 'jira-credentials-updated');
    }

    public function testConnection(): void
    {
        $this->validate([
            'jira_url' => 'required|url',
            'jira_user' => 'required|email',
            'jira_api_key' => 'required|string',
        ]);

        try {
            // Get the actual API key to use (might be placeholder if user didn't change it)
            $user = auth()->user();
            $currentApiKey = $user->jira_api_key ?? '';
            $length = strlen($currentApiKey);
            $placeholderStars = str_repeat('•', $length);

            // Use the original API key if the entered value is the placeholder
            $apiKeyToUse = ($this->jira_api_key === $placeholderStars)
                ? $currentApiKey
                : $this->jira_api_key;

            // Create a temporary user with the entered credentials for testing
            $tempUser = new \App\Models\User();
            $tempUser->jira_url = $this->jira_url;
            $tempUser->jira_user = $this->jira_user;
            $tempUser->jira_api_key = $apiKeyToUse;

            $jiraService = new JiraService($tempUser);
            $result = $jiraService->testConnection();

            $this->connectionTested = true;
            $this->connectionSuccessful = $result['success'];

            if ($result['success']) {
                $username = $result['username'] ?? '';
                $this->connectionMessage = 'Connection successful! Connected as ' . ($username ?: 'authenticated user') . '.';
            } else {
                $this->connectionMessage = 'Connection failed. Please check your credentials.';
            }
        } catch (\Exception $e) {
            $this->connectionTested = true;
            $this->connectionSuccessful = false;
            $this->connectionMessage = 'Connection failed: ' . $e->getMessage();
        }
    }
}
