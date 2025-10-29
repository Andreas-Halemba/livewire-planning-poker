<section class="rounded-box">
    <header>
        <h2 class="text-lg font-medium">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-base-content">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex flex-col gap-2">
            <x-input-label for="name" :value="__('Name')" class="text-base-content" />
            <x-text-input :value="old('name', $user->name)" id="name" name="name" autocomplete="none" required
                autofocus />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="flex flex-col gap-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input :value="old('name', $user->email)" id="email" name="email" autocomplete="none" required
                autofocus />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-primary btn-sm">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-2">
            <x-input-label for="theme" :value="__('Theme')" />
            <select id="theme" data-choose-theme class="w-full select">
                <option value="corporate">Corporate</option>
                <option value="fantasy">Fantasy</option>
                <option value="business">Business</option>
                <option value="dracula">Dark</option>
                <option value="light">Light</option>
            </select>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="w-full">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
