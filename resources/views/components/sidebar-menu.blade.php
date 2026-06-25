{{-- Shared nav content — included in both desktop sidebar and mobile drawer --}}
<div class="flex flex-col flex-1 min-h-0" x-data="{
    open: {
        danhmuc: {{ in_array($activeModule, ['danhmuc']) ? 'true' : 'false' }},
        baocao:  {{ in_array($activeModule, ['baocao'])  ? 'true' : 'false' }},
        caidat:  {{ in_array($activeModule, ['caidat'])  ? 'true' : 'false' }},
    }
}">

<nav class="flex-1 px-3 py-3 overflow-y-auto overflow-x-hidden">

    {{-- ── TỔNG QUAN ──────────────────────────────────────── --}}
    <p class="sidebar-section-label">Tổng quan</p>

    <a href="{{ route('dashboard') }}"
       data-tooltip="Dashboard"
       class="sidebar-nav-item {{ $activeModule === 'tongquan' ? 'active' : '' }}">
        <i class="bi bi-speedometer2 text-base w-5 text-center flex-shrink-0"></i>
        <span class="nav-text">Dashboard</span>
    </a>

    {{-- ── NGHIỆP VỤ ───────────────────────────────────────── --}}
    <p class="sidebar-section-label sidebar-section-divider">Nghiệp vụ</p>

    {{-- Danh mục (accordion) --}}
    <div>
        <button @click="open.danhmuc = !open.danhmuc"
                data-tooltip="Danh mục"
                class="sidebar-nav-item {{ $activeModule === 'danhmuc' ? 'active' : '' }} justify-between">
            <span class="flex items-center gap-2.5 min-w-0">
                <i class="bi bi-grid text-base w-5 text-center flex-shrink-0"></i>
                <span class="nav-text">Danh mục</span>
            </span>
            <i class="bi nav-chevron text-xs transition-transform duration-200 flex-shrink-0"
               :class="open.danhmuc ? 'bi-chevron-down' : 'bi-chevron-right'"
               style="color:var(--sidebar-text)"></i>
        </button>
        <div x-show="open.danhmuc"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-0.5 space-y-0.5">
            @can('view-categories')
            <a href="{{ route('categories.index') }}"
               class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'categories') ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Danh mục sản phẩm
            </a>
            @endcan
            @can('view-products')
            <a href="{{ route('products.index') }}"
               class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'products') ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Sản phẩm
            </a>
            @endcan
            @can('view-units')
            <a href="{{ route('units.index') }}"
               class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'units') ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Đơn vị tính
            </a>
            @endcan
            @can('view-suppliers')
            <a href="{{ route('suppliers.index') }}"
               class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'suppliers') ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Nhà cung cấp
            </a>
            @endcan
            @can('manage-destinations')
            <a href="{{ route('destinations.index') }}"
               class="sidebar-child-item {{ str_starts_with($routeName ?? '', 'destinations') ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Kho nhận hàng
            </a>
            @endcan
        </div>
    </div>

    {{-- Giao dịch: Nhập / Xuất / Kiểm kê --}}
    @canany(['view-transactions', 'view-stocktakes'])
    @can('view-transactions')
    <a href="{{ route('transactions.index', ['type' => 'IN']) }}"
       data-tooltip="Nhập kho"
       class="sidebar-nav-item mt-0.5 {{ $routeName === 'transactions.index' && request('type') === 'IN' ? 'active' : '' }}">
        <i class="bi bi-arrow-down-circle text-base w-5 text-center flex-shrink-0"></i>
        <span class="nav-text">Nhập kho</span>
    </a>

    <a href="{{ route('transactions.index', ['type' => 'OUT']) }}"
       data-tooltip="Xuất kho"
       class="sidebar-nav-item mt-0.5 {{ $routeName === 'transactions.index' && request('type') === 'OUT' ? 'active' : '' }}">
        <i class="bi bi-arrow-up-circle text-base w-5 text-center flex-shrink-0"></i>
        <span class="nav-text">Xuất kho</span>
    </a>

    @if(\App\Models\Setting::get('require_approval', true))
    <a href="{{ route('transactions.index', ['status' => 'pending']) }}"
       data-tooltip="Chờ duyệt"
       class="sidebar-nav-item mt-0.5 {{ $routeName === 'transactions.index' && request('status') === 'pending' ? 'active' : '' }}">
        <i class="bi bi-hourglass-split text-base w-5 text-center flex-shrink-0"></i>
        <span class="nav-text">Chờ duyệt</span>
    </a>
    @endif
    @endcan

    @can('view-stocktakes')
    <a href="{{ route('stocktakes.index') }}"
       data-tooltip="Kiểm kê"
       class="sidebar-nav-item mt-0.5 {{ str_starts_with($routeName ?? '', 'stocktakes') ? 'active' : '' }}">
        <i class="bi bi-clipboard-check text-base w-5 text-center flex-shrink-0"></i>
        <span class="nav-text">Kiểm kê</span>
    </a>
    @endcan
    @endcanany

    {{-- ── TỒN KHO ─────────────────────────────────────────── --}}
    @canany(['view-inventory', 'view-stock-ledger'])
    <p class="sidebar-section-label sidebar-section-divider">Tồn kho</p>

    @can('view-inventory')
    <a href="{{ route('inventory.index') }}"
       data-tooltip="Tồn kho"
       class="sidebar-nav-item {{ $routeName === 'inventory.index' ? 'active' : '' }}">
        <i class="bi bi-boxes text-base w-5 text-center flex-shrink-0"></i>
        <span class="nav-text">Tồn kho</span>
    </a>
    @endcan

    @can('view-stock-ledger')
    <a href="{{ route('stock-ledger.index') }}"
       data-tooltip="Thẻ kho"
       class="sidebar-nav-item mt-0.5 {{ $routeName === 'stock-ledger.index' ? 'active' : '' }}">
        <i class="bi bi-journal-text text-base w-5 text-center flex-shrink-0"></i>
        <span class="nav-text">Thẻ kho</span>
    </a>
    @endcan
    @endcanany

    {{-- ── BÁO CÁO ──────────────────────────────────────────── --}}
    @can('view-reports')
    <p class="sidebar-section-label sidebar-section-divider">Báo cáo</p>

    <div>
        <button @click="open.baocao = !open.baocao"
                data-tooltip="Báo cáo"
                class="sidebar-nav-item {{ $activeModule === 'baocao' ? 'active' : '' }} justify-between">
            <span class="flex items-center gap-2.5 min-w-0">
                <i class="bi bi-bar-chart-line text-base w-5 text-center flex-shrink-0"></i>
                <span class="nav-text">Báo cáo</span>
            </span>
            <i class="bi nav-chevron text-xs transition-transform duration-200 flex-shrink-0"
               :class="open.baocao ? 'bi-chevron-down' : 'bi-chevron-right'"
               style="color:var(--sidebar-text)"></i>
        </button>
        <div x-show="open.baocao"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-0.5 space-y-0.5">
            <a href="{{ route('reports.receipts') }}"
               class="sidebar-child-item {{ $routeName === 'reports.receipts' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Nhập kho
            </a>
            <a href="{{ route('reports.issues') }}"
               class="sidebar-child-item {{ $routeName === 'reports.issues' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Xuất kho
            </a>
            <a href="{{ route('reports.inventory') }}"
               class="sidebar-child-item {{ $routeName === 'reports.inventory' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Tồn kho
            </a>
            <a href="{{ route('reports.summary') }}"
               class="sidebar-child-item {{ $routeName === 'reports.summary' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Nhập xuất tồn
            </a>
            <a href="{{ route('reports.internal-debt') }}"
               class="sidebar-child-item {{ $routeName === 'reports.internal-debt' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Công nợ nội bộ
            </a>
        </div>
    </div>
    @endcan

    {{-- ── HỆ THỐNG ─────────────────────────────────────────── --}}
    @canany(['manage-users', 'view-activity-logs', 'manage-settings'])
    <p class="sidebar-section-label sidebar-section-divider">Hệ thống</p>

    <div>
        <button @click="open.caidat = !open.caidat"
                data-tooltip="Cài đặt"
                class="sidebar-nav-item {{ $activeModule === 'caidat' ? 'active' : '' }} justify-between">
            <span class="flex items-center gap-2.5 min-w-0">
                <i class="bi bi-gear text-base w-5 text-center flex-shrink-0"></i>
                <span class="nav-text">Cài đặt</span>
            </span>
            <i class="bi nav-chevron text-xs transition-transform duration-200 flex-shrink-0"
               :class="open.caidat ? 'bi-chevron-down' : 'bi-chevron-right'"
               style="color:var(--sidebar-text)"></i>
        </button>
        <div x-show="open.caidat"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-0.5 space-y-0.5">
            @can('manage-settings')
            <a href="{{ route('settings.index') }}"
               class="sidebar-child-item {{ $routeName === 'settings.index' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Cài đặt hệ thống
            </a>
            @endcan
            @can('manage-users')
            <a href="{{ route('users.index') }}"
               class="sidebar-child-item {{ $routeName === 'users.index' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Người dùng
            </a>
            @endcan
            @can('view-activity-logs')
            <a href="{{ route('activity-logs.index') }}"
               class="sidebar-child-item {{ $routeName === 'activity-logs.index' ? 'active' : '' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-40 flex-shrink-0"></span>
                Lịch sử hoạt động
            </a>
            @endcan
        </div>
    </div>
    @endcanany

</nav>

{{-- User footer --}}
<div class="px-3 py-4 border-t flex-shrink-0 overflow-hidden" style="border-color: var(--sidebar-border)">
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="sidebar-nav-item w-full" data-tooltip="Tài khoản">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 text-white"
                 style="background: var(--sidebar-accent)">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="nav-text flex-1 text-left min-w-0">
                <p class="text-xs font-semibold truncate" style="color: var(--text-primary)">{{ auth()->user()?->name }}</p>
                <p class="text-[10px] truncate" style="color: var(--sidebar-text)">{{ auth()->user()?->getRoleNames()->first() }}</p>
            </div>
            <i class="bi bi-three-dots-vertical nav-text text-xs flex-shrink-0" style="color:var(--sidebar-text)"></i>
        </button>

        <div x-show="open" @click.outside="open = false" x-transition
             class="absolute bottom-full left-0 right-0 mb-1 rounded-xl shadow-xl overflow-hidden"
             style="background: var(--surface-card); border: 1px solid var(--sidebar-border); box-shadow: 0 8px 32px rgba(0,0,0,0.12)">
            <a href="{{ route('profile.edit') }}"
               class="sidebar-nav-item rounded-none text-xs py-2.5" style="border-radius:0">
                <i class="bi bi-person w-4 text-center"></i> Hồ sơ
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-nav-item rounded-none text-xs py-2.5 w-full" style="color:#EF4444; border-radius:0">
                    <i class="bi bi-box-arrow-right w-4 text-center"></i> Đăng xuất
                </button>
            </form>
        </div>
    </div>
</div>

</div>
