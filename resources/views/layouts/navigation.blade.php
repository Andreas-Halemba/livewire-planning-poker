<nav x-data="{ open: false }" class="border-b border-base-300 bg-base-200 text-base-content">
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('dashboard') }}">
                        <svg width="280" height="70" viewBox="0 0 280 70" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="hGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#6366F1;stop-opacity:1"></stop>
                                    <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:1"></stop>
                                </linearGradient>
                                <linearGradient id="hGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#10B981;stop-opacity:1"></stop>
                                    <stop offset="100%" style="stop-color:#06B6D4;stop-opacity:1"></stop>
                                </linearGradient>
                                <linearGradient id="hGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#F59E0B;stop-opacity:1"></stop>
                                    <stop offset="100%" style="stop-color:#EF4444;stop-opacity:1"></stop>
                                </linearGradient>
                                <linearGradient id="hTextGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#6366F1;stop-opacity:1"></stop>
                                    <stop offset="50%" style="stop-color:#8B5CF6;stop-opacity:1"></stop>
                                    <stop offset="100%" style="stop-color:#06B6D4;stop-opacity:1"></stop>
                                </linearGradient>
                            </defs>

                            <!-- Decorative elements -->
                            <circle cx="5" cy="35" r="2" fill="#F59E0B" opacity="0.6"></circle>
                            <circle cx="275" cy="25" r="1.5" fill="#06B6D4" opacity="0.6"></circle>
                            <path d="M 270 40 L 271 42 L 273 43 L 271 44 L 270 46 L 269 44 L 267 43 L 269 42 Z"
                                fill="#10B981" opacity="0.7"></path>

                            <!-- Cards -->
                            <g>
                                <rect x="30" y="10" width="30" height="42" rx="5" fill="url(#hGrad3)"
                                    transform="rotate(10 45 31)"></rect>
                                <text x="45" y="36" font-family="Arial, sans-serif" font-size="20" font-weight="bold"
                                    fill="white" text-anchor="middle" transform="rotate(10 45 36)">?</text>

                                <rect x="25" y="10" width="30" height="42" rx="5" fill="url(#hGrad2)"></rect>
                                <text x="40" y="36" font-family="Arial, sans-serif" font-size="20" font-weight="bold"
                                    fill="white" text-anchor="middle">5</text>

                                <rect x="20" y="10" width="30" height="42" rx="5" fill="url(#hGrad1)"
                                    transform="rotate(-10 35 31)"></rect>
                                <text x="35" y="36" font-family="Arial, sans-serif" font-size="20" font-weight="bold"
                                    fill="white" text-anchor="middle" transform="rotate(-10 35 36)">8</text>
                            </g>

                            <!-- Text -->
                            <text x="75" y="42" font-family="Arial, sans-serif" font-size="24" font-weight="800"
                                fill="url(#hTextGrad)">Scrum Poker</text>

                            <!-- Decorative underline -->
                            <path d="M 75 48 Q 140 50 205 48" stroke="url(#hTextGrad)" stroke-width="2" fill="none"
                                opacity="0.3"></path>
                        </svg>
                    </a>
                </div>
                {{-- <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div> --}}
            </div>
            <div class="flex items-center gap-1">
                <x-theme-switcher class="self-end" />

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex">
                    <div class="dropdown dropdown-end">
                        <label tabindex="0"
                            class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity p-1 rounded-full hover:bg-base-300/50">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-primary text-primary-content rounded-full w-9 h-9 flex items-center justify-center">
                                    <span class="text-xs font-semibold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1) . (str_contains(Auth::user()->name, ' ') ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : '')) }}
                                    </span>
                                </div>
                            </div>
                            <span
                                class="hidden md:inline text-sm font-medium text-base-content">{{ Auth::user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/60" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </label>
                        <ul tabindex="0"
                            class="p-2 shadow-lg dropdown-content menu bg-base-100 border border-base-300 rounded-box w-56 mt-2">
                            <li class="menu-title">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="text-xs text-base-content/70">{{ Auth::user()->email }}</span>
                            </li>
                            <div class="divider my-1"></div>
                            <li>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ __('Profile') }}
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault();
                                                    this.closest('form').submit();"
                                        class="flex items-center gap-2 text-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ __('Log Out') }}
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center -mr-2 sm:hidden">
                    <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-md text-base-content hover:bg-base-300 focus:bg-base-300">
                        <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-base-300">
            <div class="px-4 flex items-center gap-3">
                <div class="avatar placeholder">
                    <div
                        class="bg-primary text-primary-content rounded-full w-10 h-10 flex items-center justify-center">
                        <span class="text-sm font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1) . (str_contains(Auth::user()->name, ' ') ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : '')) }}
                        </span>
                    </div>
                </div>
                <div>
                    <div class="text-base font-medium text-base-content">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-base-content/70">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
