<x-guest-layout>
    <h1 class="text-lg">Register</h1>
    <form
        method="POST"
        action="{{ route('register') }}"
        class="flex flex-col space-y-4 w-42"
    >
        @csrf

        <!-- Name -->
        <div class="form-control">
            <input
                id="name"
                name="name"
                class="w-full input input-bordered"
                placeholder="Name"
                type="text"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error
                :messages="$errors->get('name')"
                class="mt-2"
            />
        </div>
        <div class="form-control">
            <input
                id="email"
                name="email"
                class="w-full input input-bordered"
                placeholder="Email"
                type="text"
                required
                autofocus
                autocomplete="email"
            />
            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        <div class="form-control">
            <input
                id="password"
                name="password"
                class="w-full input input-bordered"
                placeholder="Password"
                type="password"
                required
                autofocus
                autocomplete="password"
            />
            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <div class="form-control">
            <input
                id="password_confirmation"
                name="password_confirmation"
                class="w-full input input-bordered"
                placeholder="Confirm Password"
                type="password"
                required
                autofocus
                autocomplete="password_confirmation"
            />
            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="mt-2"
            />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a
                class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}"
            >
                {{ __('Already registered?') }}
            </a>

            <button class="ml-4 btn btn-primary btn-sm">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>
