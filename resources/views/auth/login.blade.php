<x-guest-layout>
    <h2 class="card-title">Login</h2>

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
                id="email"
                type="email"
                name="email"
                placeholder="Email"
                class="w-full input input-bordered"
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
                id="password"
                type="password"
                name="password"
                placeholder="Password"
                class="w-full input input-bordered"
            />
            <label class="label">
                <span class="label-text-alt">
                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2"
                    />
                </span>
            </label>
        </div>

        <!-- Remember Me -->
        <div class="form-control">
            <label
                class="cursor-pointer label"
                for="remember_me"
            >
                <span class="label-text">{{ __('Remember me') }}</span>
                <input
                    id="remember_me"
                    name="remember_me"
                    type="checkbox"
                    class="checkbox checkbox-accent"
                />
            </label>
        </div>

        <div class="flex items-center justify-end gap-2 mt-4">
            @if (Route::has('password.request'))
                <a
                    class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}"
                >
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <button
                type="submit"
                class="btn btn-accent btn-sm "
            >Login</button>
        </div>
    </form>
    <div class="divider">OR</div>
    <h2 class="card-title">Register</h2>
    <a
        class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        href="{{ route('register') }}"
    >
        {{ __('Create an account') }}
    </a>
</x-guest-layout>
