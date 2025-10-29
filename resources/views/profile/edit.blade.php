<x-app-layout>
    <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
        <div class="p-4 shadow bg-base-300 sm:p-8 sm:rounded-lg">
            <div>
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 shadow bg-base-300 sm:p-8 sm:rounded-lg">
            <div>
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 shadow bg-base-300 sm:p-8 sm:rounded-lg">
            <div>
                @livewire('profile.jira-credentials')
            </div>
        </div>

        <div class="p-4 shadow bg-base-300 sm:p-8 sm:rounded-lg">
            <div>
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
