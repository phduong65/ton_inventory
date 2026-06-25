<header class="topbar">

    {{-- Mobile hamburger (chỉ hiện mobile < lg, ẩn desktop) --}}
    <div class="topbar-hamburger-wrap flex-shrink-0 mr-1">
        <button @click="mobileMenuOpen = true"
                class="topbar-icon-btn"
                aria-label="Mở menu">
            <i class="bi bi-list text-lg"></i>
        </button>
    </div>

    {{-- Desktop sidebar collapse toggle (ẩn mobile, chỉ hiện desktop >= lg) --}}
    <div class="topbar-collapse-wrap flex-shrink-0 mr-1">
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="topbar-icon-btn"
                :title="sidebarCollapsed ? 'Mở rộng menu' : 'Thu gọn menu'"
                aria-label="Toggle sidebar">
            <i class="bi bi-layout-sidebar text-lg"></i>
        </button>
    </div>

    {{-- Left: Page title + breadcrumb --}}
    <div class="flex-1 min-w-0">
        @hasSection('page-title')
        <h1 class="text-sm font-semibold truncate" style="color:var(--text-primary)">@yield('page-title')</h1>
        @endif
        @hasSection('breadcrumb')
        <p class="text-xs truncate hidden sm:block" style="color:var(--text-muted)">@yield('breadcrumb')</p>
        @endif
    </div>

    {{-- Center: Search (md+) --}}
    <div class="hidden md:flex items-center">
        <div class="relative">
            <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-xs pointer-events-none" style="color:var(--text-muted)"></i>
            <input type="text"
                   placeholder="Tìm kiếm..."
                   class="topbar-search"
                   readonly
                   tabindex="-1">
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-0.5 text-[10px] font-medium px-1.5 py-0.5 rounded pointer-events-none"
                  style="background:var(--surface-border); color:var(--text-muted)">
                Ctrl F
            </span>
        </div>
    </div>

    {{-- Right: Actions --}}
    <div class="flex items-center gap-1">

        {{-- Notification bell --}}
        <button type="button" class="topbar-icon-btn" title="Thông báo">
            <i class="bi bi-bell text-sm"></i>
        </button>

        <div class="w-px h-5 mx-1" style="background:var(--surface-border)"></div>

        {{-- Theme toggle --}}
        <form action="{{ route('profile.theme') }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="theme" value="{{ auth()->user()?->theme === 'dark' ? 'light' : 'dark' }}">
            <button type="submit" class="topbar-icon-btn" title="Đổi giao diện">
                @if(auth()->user()?->theme === 'dark')
                    <i class="bi bi-sun text-sm"></i>
                @else
                    <i class="bi bi-moon text-sm"></i>
                @endif
            </button>
        </form>

        <div class="w-px h-5 mx-1" style="background:var(--surface-border)"></div>

        {{-- User dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-white/5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 text-white"
                     style="background: var(--sidebar-accent)">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-semibold leading-tight" style="color:var(--text-primary)">{{ auth()->user()?->name }}</p>
                    <p class="text-[10px] leading-tight" style="color:var(--text-muted)">{{ auth()->user()?->getRoleNames()->first() }}</p>
                </div>
                <i class="bi bi-chevron-down text-xs" style="color:var(--text-muted)"></i>
            </button>

            <div x-show="open" @click.outside="open = false" x-transition
                 class="absolute right-0 top-full mt-1.5 w-44 rounded-xl overflow-hidden z-50"
                 style="background:var(--surface-card); border: 1px solid var(--surface-border); box-shadow: 0 8px 32px rgba(0,0,0,0.12)">
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm transition-colors"
                   style="color:var(--text-secondary)"
                   onmouseover="this.style.background='var(--surface-bg)'"
                   onmouseout="this.style.background=''">
                    <i class="bi bi-person text-sm"></i> Hồ sơ
                </a>
                <div class="h-px mx-3" style="background:var(--surface-border)"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm w-full text-left transition-colors"
                            style="color:#EF4444"
                            onmouseover="this.style.background='rgba(239,68,68,0.06)'"
                            onmouseout="this.style.background=''">
                        <i class="bi bi-box-arrow-right text-sm"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
