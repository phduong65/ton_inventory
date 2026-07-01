{{-- ── Desktop Sidebar (lg+) ──────────────────────────────────── --}}
<aside class="sidebar-base hidden lg:flex flex-col" :class="sidebarCollapsed ? 'sidebar-collapsed' : ''">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-3 py-3 border-b flex-shrink-0 overflow-hidden"
        style="border-color: var(--sidebar-border)">
        <a href="/" class="flex items-center justify-center">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                <img src="{{ asset('/assets/images/logo.png') }}" alt="logo" class="w-full h-full object-contain">
            </div>
        </a>
        <div x-show="!sidebarCollapsed" x-transition:enter="transition-opacity duration-150"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <p class="text-md font-bold leading-tight whitespace-nowrap" style="color:var(--text-primary)">Quản Lý Kho
            </p>
        </div>
    </div>

    @include('components.sidebar-menu', ['activeModule' => $activeModule, 'routeName' => $routeName])

</aside>

{{-- ── Mobile Sidebar Drawer (< lg) ──────────────────────────── --}}
<div x-show="mobileMenuOpen" @keydown.escape.window="mobileMenuOpen = false" class="fixed inset-0 z-50 lg:hidden flex"
    x-cloak>

    {{-- Backdrop --}}
    <div @click="mobileMenuOpen = false" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    {{-- Drawer Panel --}}
    <div class="relative flex flex-col h-full overflow-hidden flex-shrink-0"
        style="width: min(280px, 82vw); background:var(--sidebar-bg); border-right:1px solid var(--sidebar-border)"
        x-transition:enter="transition-transform ease-out duration-250" x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">

        {{-- Logo + Close --}}
        <div class="flex items-center justify-between px-3 py-3 border-b flex-shrink-0"
            style="border-color: var(--sidebar-border)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                    <img src="{{ asset('/assets/images/logo.png') }}" alt="logo"
                        class="w-full h-full object-contain">
                </div>
                <p class="text-md font-bold leading-tight" style="color:var(--text-primary)">Quản Lý Kho</p>
            </div>
            <button @click="mobileMenuOpen = false" class="topbar-icon-btn flex-shrink-0" aria-label="Đóng menu">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        @include('components.sidebar-menu', ['activeModule' => $activeModule, 'routeName' => $routeName])

    </div>
</div>
