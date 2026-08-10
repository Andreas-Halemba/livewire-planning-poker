<x-guest-layout>
    <h1 class="justify-center w-full card-title">Register</h1>
    <form method="POST" action="{{ route('register') }}" class="flex flex-col space-y-4 w-full">
        @csrf

        <!-- Name -->
        <div class="flex flex-col gap-2">
            <x-text-input value="{{ old('name') }}" id="name" name="name" placeholder="Name" type="text"
                required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div class="flex flex-col gap-2">
            <x-text-input value="{{ old('email') }}" id="email" name="email" placeholder="Email" type="email"
                required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-2">
            <x-text-input id="password" name="password" placeholder="Password" type="password" required autofocus
                autocomplete="password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-2">
            <x-text-input id="password_confirmation" name="password_confirmation" placeholder="Confirm Password"
                type="password" required autofocus autocomplete="password_confirmation" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-2">
            <div class="cf-turnstile self-center" data-sitekey="{{ config('services.turnstile.site_key') }}" data-action="{{ config('services.turnstile.action') }}"></div>
            <x-input-error :messages="$errors->get('cf-turnstile-response')" />
        </div>

        <div class="flex flex-col align-center justify-center gap-2 mt-2">
            <button class="btn btn-primary cursor-pointer">
                {{ __('Register') }}
            </button>
            <a class="self-center text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
        </div>
    </form>

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</x-guest-layout>
