<x-guest-layout>
    <h2 class="justify-center w-full card-title">Login</h2>

    <!-- Session Status -->
    <x-auth-session-status
        class="mb-4"
        :status="session('status')"
    />

    <form
        method="POST"
        action="{{ route('login') }}"
        class="flex flex-col space-y-2 w-42"
    >
        @csrf

        <!-- Email Address -->
        <div class="w-full form-control">
            <input
                autofocus
                required
                value="{{ old('email') }}"
                id="email"
                type="email"
                name="email"
                placeholder="Email"
                class="w-full input bg-base-300 input-bordered border-primary focus:bg-white"
                autocomplete="email"
            />
            <label class="label">
                <span class="label-text-alt">
                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-2"
                    />
                </span>
            </label>
        </div>

        <div class="w-full form-control">
            <input
                required
                id="password"
                type="password"
                name="password"
                placeholder="Password"
                class="w-full input bg-base-300 input-bordered border-primary focus:bg-white"
                autocomplete="password"
            />
            <label class="label">
                <span class="label-text-alt">
                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2"
                    />
                </span>
            </label>
            @if (Route::has('password.request'))
            <a
                class="text-sm text-center link link-hover"
                href="{{ route('password.request') }}"
            >
                {{ __('Forgot your password?') }}
            </a>
        @endif
        </div>

        <!-- Remember Me -->
        <div class="form-control">
            <label
                class="flex justify-center gap-2 cursor-pointer label"
                for="remember_me"
            >
                <span class="label-text">{{ __('Remember me') }}</span>
                <input
                    id="remember_me"
                    name="remember_me"
                    type="checkbox"
                    class="checkbox checkbox-primary"
                />
            </label>
        </div>

        <div class="flex flex-col items-center justify-center gap-2 mt-4">
            <button
                type="submit"
                class="btn btn-primary"
            >Login</button>
        </div>
    </form>
    <div class="divider">OR</div>
    <div class="flex flex-col items-center justify-center gap-2">
        <a
            class="btn btn-secondary"
            href="{{ route('register') }}"
        >
            {{ __('Create an account') }}
        </a>
    </div>
</x-guest-layout>
