{{-- ── Quick Actions Bar (desktop lg+) ──────────────────────── --}}
<div class="hidden lg:flex items-center gap-0.5 border-b flex-shrink-0 px-4"
     style="background:var(--surface-card); border-color:var(--surface-border); height:34px">

    {{-- Label --}}
    <span class="flex items-center gap-1.5 pr-3 mr-1 flex-shrink-0" style="border-right:1px solid var(--surface-border)">
        <i class="bi bi-lightning-charge-fill" style="font-size:10px; color:#6366f1"></i>
        <span style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:var(--text-muted)">Thao tác</span>
    </span>

    @can('create-transactions')
    <a href="{{ route('transactions.create', ['type' => 'IN']) }}"
       class="qa-btn" style="--qa-c:#16a34a; --qa-bg:rgba(22,163,74,.09)">
        <i class="bi bi-arrow-down-circle-fill" style="font-size:11px"></i> Phiếu nhập
    </a>
    @endcan

    @can('create-transactions')
    <a href="{{ route('transactions.create', ['type' => 'OUT']) }}"
       class="qa-btn" style="--qa-c:#ea580c; --qa-bg:rgba(234,88,12,.09)">
        <i class="bi bi-arrow-up-circle-fill" style="font-size:11px"></i> Phiếu xuất
    </a>
    @endcan

    <span class="qa-sep"></span>

    @can('view-inventory')
    <a href="{{ route('inventory.index') }}"
       class="qa-btn" style="--qa-c:#4338ca; --qa-bg:rgba(67,56,202,.09)">
        <i class="bi bi-boxes" style="font-size:11px"></i> Tồn kho
    </a>
    @endcan

    @can('approve-transactions')
    <a href="{{ route('transactions.index', ['status' => 'pending']) }}"
       class="qa-btn" style="--qa-c:#b45309; --qa-bg:rgba(180,83,9,.09)">
        <i class="bi bi-hourglass-split" style="font-size:11px"></i> Chờ duyệt
        @if(($pendingCount ?? 0) > 0)
        <span class="qa-badge">{{ $pendingCount }}</span>
        @endif
    </a>
    @endcan

    <span class="qa-sep"></span>

    @can('create-stocktakes')
    <a href="{{ route('stocktakes.index') }}"
       class="qa-btn" style="--qa-c:#7c3aed; --qa-bg:rgba(124,58,237,.09)">
        <i class="bi bi-clipboard-check" style="font-size:11px"></i> Kiểm kê
    </a>
    @endcan

    @can('view-reports')
    <a href="{{ route('reports.inventory') }}"
       class="qa-btn" style="--qa-c:#1d4ed8; --qa-bg:rgba(29,78,216,.09)">
        <i class="bi bi-graph-up" style="font-size:11px"></i> Báo cáo
    </a>
    @endcan

</div>

<style>
.qa-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 500;
    color: var(--qa-c);
    text-decoration: none;
    flex-shrink: 0;
    white-space: nowrap;
    transition: background .12s;
}
.qa-btn:hover { background: var(--qa-bg); }
.qa-sep {
    width: 1px;
    height: 14px;
    background: var(--surface-border);
    flex-shrink: 0;
    margin: 0 4px;
}
.qa-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    border-radius: 99px;
    background: #f59e0b;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
}

/* ── FAB action pills (mobile) ── */
.fab-action {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px 9px 12px;
    border-radius: 99px;
    box-shadow: 0 2px 12px rgba(0,0,0,.10);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: transform .15s, box-shadow .15s;
}
.fab-action:active {
    transform: scale(.97);
    box-shadow: 0 1px 6px rgba(0,0,0,.08);
}
.fab-action i { font-size: 15px; flex-shrink: 0; }
.fab-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 99px;
    background: #f59e0b;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    border: 2px solid #fff;
}
</style>

{{-- ── Mobile FAB (< lg) — hiển thị trên tất cả trang ──────── --}}
<div x-data="{ fabOpen: false }"
     @keydown.escape.window="fabOpen = false"
     class="fixed bottom-6 right-6 z-50 lg:hidden flex flex-col items-end">

    {{-- Backdrop --}}
    <div x-show="fabOpen"
         x-transition:enter="transition-opacity duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         @click="fabOpen = false"
         class="fixed inset-0 bg-black/25 backdrop-blur-[1px]"
         style="z-index:-1"></div>

    {{-- Action icons --}}
    <div x-show="fabOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         x-cloak
         class="flex flex-col items-end gap-2.5 mb-3">

        @can('view-reports')
        <a href="{{ route('reports.inventory') }}"
           class="fab-action" style="background:#dbeafe; border:1px solid #bfdbfe">
            <i class="bi bi-graph-up" style="color:#1d4ed8"></i>
            <span style="color:#1d4ed8">Báo cáo tồn</span>
        </a>
        @endcan

        @can('create-stocktakes')
        <a href="{{ route('stocktakes.index') }}"
           class="fab-action" style="background:#ede9fe; border:1px solid #ddd6fe">
            <i class="bi bi-clipboard-check" style="color:#7c3aed"></i>
            <span style="color:#7c3aed">Kiểm kê</span>
        </a>
        @endcan

        @can('approve-transactions')
        <a href="{{ route('transactions.index', ['status' => 'pending']) }}"
           class="fab-action relative" style="background:#fef9c3; border:1px solid #fde68a">
            <i class="bi bi-hourglass-split" style="color:#a16207"></i>
            <span style="color:#a16207">Chờ duyệt</span>
            @if(($pendingCount ?? 0) > 0)
            <span class="fab-badge">{{ $pendingCount }}</span>
            @endif
        </a>
        @endcan

        @can('view-inventory')
        <a href="{{ route('inventory.index') }}"
           class="fab-action" style="background:#e0e7ff; border:1px solid #c7d2fe">
            <i class="bi bi-boxes" style="color:#4338ca"></i>
            <span style="color:#4338ca">Tồn kho</span>
        </a>
        @endcan

        @can('create-transactions')
        <a href="{{ route('transactions.create', ['type' => 'OUT']) }}"
           class="fab-action" style="background:#ffedd5; border:1px solid #fed7aa">
            <i class="bi bi-arrow-up-circle-fill" style="color:#c2410c"></i>
            <span style="color:#c2410c">Tạo phiếu xuất</span>
        </a>
        @endcan

        @can('create-transactions')
        <a href="{{ route('transactions.create', ['type' => 'IN']) }}"
           class="fab-action" style="background:#dcfce7; border:1px solid #bbf7d0">
            <i class="bi bi-arrow-down-circle-fill" style="color:#16a34a"></i>
            <span style="color:#16a34a">Tạo phiếu nhập</span>
        </a>
        @endcan

    </div>

    {{-- FAB Button --}}
    <button @click="fabOpen = !fabOpen"
            class="w-14 h-14 rounded-full flex items-center justify-center transition-all duration-200"
            :class="fabOpen ? 'rotate-45' : 'rotate-0'"
            style="background:#4f46e5; color:#fff; box-shadow:0 8px 24px rgba(79,70,229,0.5)">
        <i class="bi bi-plus-lg text-xl pointer-events-none"></i>
    </button>
</div>
