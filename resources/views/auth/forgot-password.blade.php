<x-guest-layout>
    <div class="w-42">

        <h2 class="card-title">Recover password</h2>
        <div class="mb-4 text-sm text-base-content">
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
                    class="w-full max-w-xs input input-bordered"
                />
                <x-input-error
                    :messages="$errors->get('email')"
                    class="mt-2"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <button
                    type="submit"
                    class="btn btn-accent"
                >
                    {{ __('Email Password Reset Link') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
