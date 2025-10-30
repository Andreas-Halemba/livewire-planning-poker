<div class="dropdown dropdown-end">
    <label tabindex="0" class="m-1 btn cursor-pointer">{{ Auth::user()->name }}</label>
    <ul tabindex="0" class="p-2 shadow dropdown-content menu bg-base-100 rounded-box w-52">
        <li>
            <a :href="route('profile.edit')">
                {{ __('Profile') }}
            </a>
        </li>
        <li>
            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf
                <a
                    :href="route('logout')"
                    onclick="event.preventDefault();
                                    this.closest('form').submit();"
                >
                    {{ __('Log Out') }}
                </a>
            </form>
        </li>
    </ul>
</div>
