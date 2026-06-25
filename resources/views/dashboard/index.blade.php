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
        <p class="text-2xl lg:text-3xl font-bold leading-none tabular-nums" style="color:var(--text-primary)">{{ $pendingCount }}</p>
        <p class="text-[11px] lg:text-xs mt-1" style="color:var(--text-muted)">Chờ duyệt</p>
    </div>

    <div class="stat-card stat-card-indigo">
        <div class="flex items-start justify-between mb-2 lg:mb-3">
            <div class="icon-bg-indigo w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-box-seam text-xs lg:text-sm" style="color:#6366f1"></i>
            </div>
            <span class="badge-blue text-[10px] lg:text-xs font-medium px-1.5 lg:px-2 py-0.5 rounded-full">SKU</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold leading-none tabular-nums" style="color:var(--text-primary)">{{ $totalProducts }}</p>
        <p class="text-[11px] lg:text-xs mt-1" style="color:var(--text-muted)">Sản phẩm</p>
    </div>

    <div class="stat-card stat-card-orange">
        <div class="flex items-start justify-between mb-2 lg:mb-3">
            <div class="icon-bg-orange w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-download text-xs lg:text-sm" style="color:#f97316"></i>
            </div>
            <span class="badge-orange text-[10px] lg:text-xs font-medium px-1.5 lg:px-2 py-0.5 rounded-full">Hôm nay</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold leading-none tabular-nums" style="color:var(--text-primary)">{{ $todayIn }}</p>
        <p class="text-[11px] lg:text-xs mt-1" style="color:var(--text-muted)">Phiếu nhập</p>
    </div>

    <div class="stat-card stat-card-pink">
        <div class="flex items-start justify-between mb-2 lg:mb-3">
            <div class="icon-bg-pink w-8 h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="bi bi-currency-dollar text-xs lg:text-sm" style="color:#ec4899"></i>
            </div>
            <span class="badge-purple text-[10px] lg:text-xs font-medium px-1.5 lg:px-2 py-0.5 rounded-full">VND</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold leading-none tabular-nums" style="color:var(--text-primary)">
            @php $v = $totalStockValue;
            echo $v >= 1000000000 ? number_format($v/1000000000,1).'B' : number_format($v/1000000,0).'M';
            @endphp
        </p>
        <p class="text-[11px] lg:text-xs mt-1" style="color:var(--text-muted)">Giá trị tồn kho</p>
    </div>

</div>

{{-- Charts row: Bar chart (50%) + Line chart (50%) --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Bar chart: số phiếu --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex flex-col"
         style="border-top: 3px solid #3b82f6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="font-semibold text-sm" style="color:var(--text-primary)">Phiếu nhập / xuất</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">6 tháng gần nhất</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1 text-xs" style="color:var(--text-muted)">
                    <span class="inline-block w-2.5 h-2.5 rounded-sm bg-blue-500"></span>Nhập
                </span>
                <span class="inline-flex items-center gap-1 text-xs" style="color:var(--text-muted)">
                    <span class="inline-block w-2.5 h-2.5 rounded-sm bg-orange-400"></span>Xuất
                </span>
            </div>
        </div>
        <div class="flex-1 min-h-0" style="position:relative; height:190px">
            <canvas id="txChart"></canvas>
        </div>
    </div>

    {{-- Line chart: giá trị theo kho --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex flex-col"
         style="border-top: 3px solid #10b981">
        <div class="mb-4">
            <p class="font-semibold text-sm" style="color:var(--text-primary)">Giá trị hàng hóa theo kho</p>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">6 tháng gần nhất — Kho Tổng: giá trị nhập, Kho 43/44: giá trị xuất</p>
        </div>
        <div class="flex-1 min-h-0" style="position:relative; height:190px">
            <canvas id="warehouseChart"></canvas>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--surface-border)">
            <h2 class="font-semibold text-sm" style="color:var(--text-primary)">Sắp hết hàng</h2>
            @if($lowStockItems->count())
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                {{ $lowStockItems->count() }}
            </span>
            @endif
        </div>
        <div class="flex-1 divide-y divide-gray-100 dark:divide-gray-700 overflow-y-auto" style="max-height:320px">
            @forelse($lowStockItems as $item)
            @php
                $isEmpty   = $item->quantity <= 0;
                $barColor  = $isEmpty ? '#ef4444' : '#f97316';
                $minStock  = $item->product->min_stock ?? 1;
                $pct       = $minStock > 0 ? min(100, max(0, ($item->quantity / $minStock) * 100)) : 0;
            @endphp
            <div class="flex items-center gap-3 px-5 py-2.5 relative">
                <div class="flex-shrink-0 w-0.5 self-stretch rounded-full" style="background:{{ $barColor }}"></div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium truncate" style="color:var(--text-primary)">{{ $item->product->name ?? '—' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="flex-1 h-1 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden" style="max-width:60px">
                            <div class="h-full rounded-full transition-all" style="width:{{ $pct }}%; background:{{ $barColor }}"></div>
                        </div>
                        <p class="text-[10px] font-mono" style="color:var(--text-muted)">{{ $item->product->sku ?? '' }}</p>
                    </div>
                </div>
                <div class="flex-shrink-0 text-right">
                    <span class="text-sm font-bold tabular-nums" style="color:{{ $barColor }}">
                        {{ number_format($item->quantity, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] block" style="color:var(--text-muted)">/ {{ number_format($minStock, 0, ',', '.') }}</span>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <i class="ph ph-check-circle text-3xl text-green-400 mb-2"></i>
                <p class="text-xs" style="color:var(--text-muted)">Tồn kho ổn định</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Bottom row: Low stock (1/3) + Recent transactions (2/3) --}}
<div class="grid grid-cols-1 gap-5">
    {{-- Recent Transactions --}}
    <div class=" table-container flex flex-col">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--surface-border)">
            <h2 class="font-semibold text-sm" style="color:var(--text-primary)">Phiếu gần đây</h2>
            <a href="{{ route('transactions.index') }}" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                Xem tất cả →
            </a>
        </div>
        <div class="overflow-x-auto flex-1">
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
                        <td class="px-5 py-3">
                            <a href="{{ route('transactions.show', $tx) }}"
                               class="font-mono text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                {{ $tx->code }}
                            </a>
                        </td>
                        <td class="px-5 py-3">
                            @if($tx->type === 'IN')
                            <span class="badge-green inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                                <i class="bi bi-download text-xs"></i> Nhập
                            </span>
                            @else
                            <span class="badge-orange inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                                <i class="bi bi-upload text-xs"></i> Xuất
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs" style="color:var(--text-secondary)">{{ $tx->date?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-xs" style="color:var(--text-primary)">
                            {{ $tx->supplier?->name ?? $tx->destination?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-center">
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
                            <span class="{{ $badgeClass }} inline-flex text-xs font-medium px-2 py-0.5 rounded-full">{{ $label }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm" style="color:var(--text-muted)">Chưa có phiếu nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const isDark    = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#9ca3af' : '#6b7280';

    // ── Bar chart: số phiếu nhập/xuất ──────────────────────────────
    new Chart(document.getElementById('txChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                { label: 'Nhập kho', data: @json($chartIn),  backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.7 },
                { label: 'Xuất kho', data: @json($chartOut), backgroundColor: '#f97316', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.7 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, stepSize: 1 }, beginAtZero: true }
            }
        }
    });

    // ── Line chart: giá trị hàng hóa theo kho ──────────────────────
    const fmtVND = (v) => {
        if (v >= 1_000_000_000) return (v / 1_000_000_000).toFixed(1) + ' tỷ';
        if (v >= 1_000_000)     return Math.round(v / 1_000_000) + ' tr';
        return v.toLocaleString('vi-VN');
    };

    new Chart(document.getElementById('warehouseChart'), {
        type: 'line',
        data: {
            labels: @json($warehouseLabels),
            datasets: @json($warehouseDatasets),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    labels: { color: textColor, font: { size: 12 }, boxWidth: 12, padding: 16, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + ctx.dataset.label + ': ' + fmtVND(ctx.parsed.y) + 'đ'
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        callback: (v) => fmtVND(v)
                    },
                    beginAtZero: true
                }
            }
        }
    });
})();
</script>
@endpush
