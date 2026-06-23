<header class="topbar">

    {{-- Page title / breadcrumb --}}
    <div class="flex-1 min-w-0">
        @hasSection('page-title')
        <h1 class="text-base font-semibold text-gray-900 dark:text-white truncate">@yield('page-title')</h1>
        @endif
        @hasSection('breadcrumb')
        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">@yield('breadcrumb')</p>
        @endif
    </div>

    {{-- Right actions --}}
    <div class="flex items-center gap-2">

        {{-- Theme toggle --}}
        <form action="{{ route('profile.theme') }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="theme" value="{{ auth()->user()?->theme === 'dark' ? 'light' : 'dark' }}">
            <button type="submit"
                    class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                @if(auth()->user()?->theme === 'dark')
                    <i class="bi bi-sun text-sm"></i>
                @else
                    <i class="bi bi-moon text-sm"></i>
                @endif
            </button>
        </form>

        {{-- Divider --}}
        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>

        {{-- User dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2.5 pl-1 pr-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                     style="background: var(--sidebar-accent,#4ADE80); color: #1B3829">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-semibold text-gray-800 dark:text-white leading-tight">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-gray-400 leading-tight">{{ auth()->user()?->getRoleNames()->first() }}</p>
                </div>
                <i class="bi bi-chevron-down text-xs text-gray-400"></i>
            </button>

            <div x-show="open" @click.outside="open = false" x-transition
                 class="absolute right-0 top-full mt-1.5 w-44 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-50">
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="bi bi-person text-sm"></i> Hồ sơ
                </a>
                <div class="h-px bg-gray-100 dark:bg-gray-700 mx-3"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 w-full text-left">
                        <i class="bi bi-box-arrow-right text-sm"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
