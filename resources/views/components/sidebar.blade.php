<aside class="sidebar-base hidden lg:flex" x-data="{
    open: {
        danhmuc: {{ in_array($activeModule, ['danhmuc']) ? 'true' : 'false' }},
        phieu:   {{ in_array($activeModule, ['phieu'])   ? 'true' : 'false' }},
        kho:     {{ in_array($activeModule, ['kho'])     ? 'true' : 'false' }},
        baocao:  {{ in_array($activeModule, ['baocao'])  ? 'true' : 'false' }},
        caidat:  {{ in_array($activeModule, ['caidat'])  ? 'true' : 'false' }},
    }
}">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-5 py-5 border-b" style="border-color: var(--sidebar-border)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--sidebar-accent)">
            <i class="bi bi-boxes text-sm font-bold" style="color:#1B3829"></i>
        </div>
        <div>
            <p class="text-white font-bold text-sm leading-tight">Quản Lý Kho</p>
            <p class="text-xs" style="color:var(--sidebar-text)">Kho Tổng 40</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="sidebar-nav-item {{ $activeModule === 'tongquan' ? 'active' : '' }}">
            <i class="bi bi-speedometer2 text-base w-5 text-center"></i>
            <span>Dashboard</span>
        </a>

        {{-- Danh mục group --}}
        <div>
            <button @click="open.danhmuc = !open.danhmuc"
                    class="sidebar-nav-item {{ $activeModule === 'danhmuc' ? 'active' : '' }} justify-between">
                <span class="flex items-center gap-2.5">
                    <i class="bi bi-grid text-base w-5 text-center"></i>
                    <span>Danh mục</span>
                </span>
                <i class="bi text-xs transition-transform duration-200"
                   :class="open.danhmuc ? 'bi-chevron-down' : 'bi-chevron-right'"
                   style="color:var(--sidebar-text)"></i>
            </button>
            <div x-show="open.danhmuc" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-0.5 space-y-0.5">
                @can('view-categories')
                <a href="{{ route('categories.index') }}"
                   class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'categories') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Ngành hàng
                </a>
                @endcan
                @can('view-products')
                <a href="{{ route('products.index') }}"
                   class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'products') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Sản phẩm
                </a>
                @endcan
                @can('view-suppliers')
                <a href="{{ route('suppliers.index') }}"
                   class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'suppliers') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Nhà cung cấp
                </a>
                @endcan
            </div>
        </div>

        {{-- Phiếu group --}}
        <div>
            <button @click="open.phieu = !open.phieu"
                    class="sidebar-nav-item {{ $activeModule === 'phieu' ? 'active' : '' }} justify-between">
                <span class="flex items-center gap-2.5">
                    <i class="bi bi-file-earmark-text text-base w-5 text-center"></i>
                    <span>Phiếu NK / XK</span>
                </span>
                <i class="bi text-xs transition-transform duration-200"
                   :class="open.phieu ? 'bi-chevron-down' : 'bi-chevron-right'"
                   style="color:var(--sidebar-text)"></i>
            </button>
            <div x-show="open.phieu" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-0.5 space-y-0.5">
                @can('view-transactions')
                <a href="{{ route('transactions.index') }}"
                   class="sidebar-child-item {{ $routeName === 'transactions.index' && !request('type') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Tất cả phiếu
                </a>
                <a href="{{ route('transactions.index', ['type' => 'IN']) }}"
                   class="sidebar-child-item {{ $routeName === 'transactions.index' && request('type') === 'IN' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Phiếu nhập
                </a>
                <a href="{{ route('transactions.index', ['type' => 'OUT']) }}"
                   class="sidebar-child-item {{ $routeName === 'transactions.index' && request('type') === 'OUT' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Phiếu xuất
                </a>
                <a href="{{ route('transactions.index', ['status' => 'pending']) }}"
                   class="sidebar-child-item {{ request('status') === 'pending' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Chờ duyệt
                </a>
                @endcan
                @can('view-stocktakes')
                <a href="{{ route('stocktakes.index') }}"
                   class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'stocktakes') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Kiểm kê
                </a>
                @endcan
            </div>
        </div>

        {{-- Kho group --}}
        <div>
            <button @click="open.kho = !open.kho"
                    class="sidebar-nav-item {{ $activeModule === 'kho' ? 'active' : '' }} justify-between">
                <span class="flex items-center gap-2.5">
                    <i class="bi bi-archive text-base w-5 text-center"></i>
                    <span>Kho hàng</span>
                </span>
                <i class="bi text-xs transition-transform duration-200"
                   :class="open.kho ? 'bi-chevron-down' : 'bi-chevron-right'"
                   style="color:var(--sidebar-text)"></i>
            </button>
            <div x-show="open.kho" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-0.5 space-y-0.5">
                @can('view-inventory')
                <a href="{{ route('inventory.index') }}"
                   class="sidebar-child-item {{ $routeName === 'inventory.index' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Tồn kho
                </a>
                @endcan
                @can('view-stock-ledger')
                <a href="{{ route('stock-ledger.index') }}"
                   class="sidebar-child-item {{ $routeName === 'stock-ledger.index' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Thẻ kho
                </a>
                @endcan
            </div>
        </div>

        {{-- Báo cáo group --}}
        @can('view-reports')
        <div>
            <button @click="open.baocao = !open.baocao"
                    class="sidebar-nav-item {{ $activeModule === 'baocao' ? 'active' : '' }} justify-between">
                <span class="flex items-center gap-2.5">
                    <i class="bi bi-bar-chart-line text-base w-5 text-center"></i>
                    <span>Báo cáo</span>
                </span>
                <i class="bi text-xs transition-transform duration-200"
                   :class="open.baocao ? 'bi-chevron-down' : 'bi-chevron-right'"
                   style="color:var(--sidebar-text)"></i>
            </button>
            <div x-show="open.baocao" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-0.5 space-y-0.5">
                <a href="{{ route('reports.receipts') }}"
                   class="sidebar-child-item {{ $routeName === 'reports.receipts' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Báo cáo nhập kho
                </a>
                <a href="{{ route('reports.issues') }}"
                   class="sidebar-child-item {{ $routeName === 'reports.issues' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Báo cáo xuất kho
                </a>
                <a href="{{ route('reports.inventory') }}"
                   class="sidebar-child-item {{ $routeName === 'reports.inventory' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Báo cáo tồn kho
                </a>
                <a href="{{ route('reports.summary') }}"
                   class="sidebar-child-item {{ $routeName === 'reports.summary' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Nhập xuất tồn
                </a>
                <a href="{{ route('reports.internal-debt') }}"
                   class="sidebar-child-item {{ $routeName === 'reports.internal-debt' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Công nợ nội bộ
                </a>
            </div>
        </div>
        @endcan

        {{-- Cài đặt group --}}
        @canany(['manage-users', 'view-activity-logs'])
        <div>
            <button @click="open.caidat = !open.caidat"
                    class="sidebar-nav-item {{ $activeModule === 'caidat' ? 'active' : '' }} justify-between">
                <span class="flex items-center gap-2.5">
                    <i class="bi bi-gear text-base w-5 text-center"></i>
                    <span>Cài đặt</span>
                </span>
                <i class="bi text-xs transition-transform duration-200"
                   :class="open.caidat ? 'bi-chevron-down' : 'bi-chevron-right'"
                   style="color:var(--sidebar-text)"></i>
            </button>
            <div x-show="open.caidat" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-0.5 space-y-0.5">
                @can('manage-users')
                <a href="{{ route('users.index') }}"
                   class="sidebar-child-item {{ $routeName === 'users.index' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Người dùng
                </a>
                @endcan
                @can('view-activity-logs')
                <a href="{{ route('activity-logs.index') }}"
                   class="sidebar-child-item {{ $routeName === 'activity-logs.index' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50 flex-shrink-0"></span>
                    Lịch sử hoạt động
                </a>
                @endcan
            </div>
        </div>
        @endcanany

    </nav>

    {{-- User footer --}}
    <div class="px-3 py-4 border-t" style="border-color: var(--sidebar-border)">
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="sidebar-nav-item w-full">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                     style="background: var(--sidebar-accent); color: #1B3829">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 text-left min-w-0">
                    <p class="text-white text-xs font-medium truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-xs truncate" style="color: var(--sidebar-text); font-size:11px">{{ auth()->user()?->getRoleNames()->first() }}</p>
                </div>
                <i class="bi bi-three-dots-vertical text-xs flex-shrink-0" style="color:var(--sidebar-text)"></i>
            </button>

            <div x-show="open" @click.outside="open = false" x-transition
                 class="absolute bottom-full left-0 right-0 mb-1 rounded-xl shadow-xl overflow-hidden"
                 style="background: var(--sidebar-hover); border: 1px solid var(--sidebar-border)">
                <a href="{{ route('profile.edit') }}"
                   class="sidebar-nav-item rounded-none text-xs py-2.5">
                    <i class="bi bi-person w-4 text-center"></i> Hồ sơ
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-nav-item rounded-none text-xs py-2.5 w-full" style="color:#F87171">
                        <i class="bi bi-box-arrow-right w-4 text-center"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>

</aside>
