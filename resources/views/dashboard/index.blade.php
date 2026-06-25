@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Tổng quan')

@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="stat-card stat-card-green">
        <div class="flex items-start justify-between mb-2 lg:mb-3">
            <div class="icon-bg-green w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-clock-history text-xs lg:text-sm text-primary-600"></i>
            </div>
            <span class="badge-green text-[10px] lg:text-xs font-medium px-1.5 lg:px-2 py-0.5 rounded-full">Phiếu</span>
        </div>
        <p class="text-xl lg:text-2xl font-bold leading-tight" style="color:var(--text-primary)">{{ $pendingCount }}</p>
        <p class="text-[11px] lg:text-xs mt-0.5" style="color:var(--text-muted)">Chờ duyệt</p>
    </div>

    <div class="stat-card stat-card-indigo">
        <div class="flex items-start justify-between mb-2 lg:mb-3">
            <div class="icon-bg-indigo w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-box-seam text-xs lg:text-sm" style="color:#6366f1"></i>
            </div>
            <span class="badge-blue text-[10px] lg:text-xs font-medium px-1.5 lg:px-2 py-0.5 rounded-full">SKU</span>
        </div>
        <p class="text-xl lg:text-2xl font-bold leading-tight" style="color:var(--text-primary)">{{ $totalProducts }}</p>
        <p class="text-[11px] lg:text-xs mt-0.5" style="color:var(--text-muted)">Sản phẩm</p>
    </div>

    <div class="stat-card stat-card-orange">
        <div class="flex items-start justify-between mb-2 lg:mb-3">
            <div class="icon-bg-orange w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-download text-xs lg:text-sm" style="color:#f97316"></i>
            </div>
            <span class="badge-orange text-[10px] lg:text-xs font-medium px-1.5 lg:px-2 py-0.5 rounded-full">Hôm nay</span>
        </div>
        <p class="text-xl lg:text-2xl font-bold leading-tight" style="color:var(--text-primary)">{{ $todayIn }}</p>
        <p class="text-[11px] lg:text-xs mt-0.5" style="color:var(--text-muted)">Phiếu nhập</p>
    </div>

    <div class="stat-card stat-card-pink">
        <div class="flex items-start justify-between mb-2 lg:mb-3">
            <div class="icon-bg-pink w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-currency-dollar text-xs lg:text-sm" style="color:#ec4899"></i>
            </div>
            <span class="badge-purple text-[10px] lg:text-xs font-medium px-1.5 lg:px-2 py-0.5 rounded-full">VND</span>
        </div>
        <p class="text-xl lg:text-2xl font-bold leading-tight" style="color:var(--text-primary)">
            @php $v = $totalStockValue;
            echo $v >= 1000000000 ? number_format($v/1000000000,1).'B' : number_format($v/1000000,0).'M';
            @endphp
        </p>
        <p class="text-[11px] lg:text-xs mt-0.5" style="color:var(--text-muted)">Giá trị tồn kho</p>
    </div>

</div>

{{-- Recent Transactions --}}
<div class="table-container">
    <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--surface-border)">
        <h2 class="font-semibold text-sm" style="color:var(--text-primary)">Phiếu gần đây</h2>
        <a href="{{ route('transactions.index') }}" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
            Xem tất cả →
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="table-header">
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide" style="color:var(--text-muted)">Số phiếu</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide" style="color:var(--text-muted)">Loại</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide" style="color:var(--text-muted)">Ngày</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide" style="color:var(--text-muted)">Đối tác</th>
                    <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wide" style="color:var(--text-muted)">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $tx)
                <tr class="table-row-divider hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('transactions.show', $tx) }}"
                           class="font-mono text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                            {{ $tx->code }}
                        </a>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($tx->type === 'IN')
                        <span class="badge-green inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full">
                            <i class="bi bi-download text-xs"></i> Nhập
                        </span>
                        @else
                        <span class="badge-orange inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full">
                            <i class="bi bi-upload text-xs"></i> Xuất
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-secondary)">{{ $tx->date?->format('d/m/Y') }}</td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-primary)">
                        {{ $tx->supplier?->name ?? $tx->destination?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @php $badgeClass = match($tx->status) {
                            'draft'    => 'badge-gray',
                            'pending'  => 'badge-yellow',
                            'approved' => 'badge-green',
                            'rejected' => 'badge-red',
                        };
                        $label = match($tx->status) {
                            'draft' => 'Nháp', 'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt', 'rejected' => 'Từ chối',
                        };
                        @endphp
                        <span class="{{ $badgeClass }} inline-flex text-xs font-medium px-2.5 py-1 rounded-full">{{ $label }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-sm" style="color:var(--text-muted)">Chưa có phiếu nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
