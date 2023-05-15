<x-guest-layout>
    <h2 class="justify-center card-title">Recover password</h2>
    <div class="mb-4 text-sm text-center text-base-content">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status
        class="mb-4"
        :status="session('status')"
    />

    <form
        method="POST"
        action="{{ route('password.email') }}"
    >
        @csrf

        <!-- Email Address -->
        <div>
            <input
                name="email"
                type="text"
                placeholder="Email"
                class="w-full input bg-base-300 input-bordered border-primary focus:bg-white"
            />
            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        <div class="flex items-center justify-center mt-4">
            <button
                type="submit"
                class="btn btn-primary"
            >
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
