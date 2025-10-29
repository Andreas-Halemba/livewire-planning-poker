<x-guest-layout>
    <h2 class="justify-center w-full card-title">Login</h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="flex flex-col space-y-4 w-full">
        @csrf

        <!-- Email Address -->
        <div class="flex flex-col gap-2">
            <x-text-input autofocus required value="{{ old('email') }}" id="email" type="email" name="email"
                placeholder="Email" autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-2">
            <x-text-input required id="password" type="password" name="password" placeholder="Password"
                autocomplete="password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            @if (Route::has('password.request'))
                <a class="text-sm text-center link link-hover" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <!-- Remember Me -->
        <div>
            <label class="flex justify-center gap-2 cursor-pointer label" for="remember_me">
                <span>{{ __('Remember me') }}</span>
                <input id="remember_me" name="remember_me" type="checkbox" class="checkbox checkbox-primary" />
            </label>
        </div>

        <div class="flex flex-col items-center justify-center gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
    <div class="divider">OR</div>
    <div class="flex flex-col items-center justify-center gap-2">
        <a class="btn btn-secondary" href="{{ route('register') }}">
            {{ __('Create an account') }}
        </a>
    </div>
</x-guest-layout>
