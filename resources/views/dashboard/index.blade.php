@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Tổng quan')

@section('content')

{{-- ── Stat Cards ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(245,158,11,0.10)">
                <i class="ph ph-hourglass text-lg" style="color:#f59e0b"></i>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(245,158,11,0.10);color:#d97706">Phiếu</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold tracking-tight tabular-nums leading-none" style="color:var(--text-primary)">
            {{ $pendingCount }}
        </p>
        <p class="text-xs mt-2" style="color:var(--text-muted)">Chờ duyệt</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(99,102,241,0.10)">
                <i class="ph ph-package text-lg" style="color:#6366f1"></i>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(99,102,241,0.10);color:#4f46e5">SKU</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold tracking-tight tabular-nums leading-none" style="color:var(--text-primary)">
            {{ $totalProducts }}
        </p>
        <p class="text-xs mt-2" style="color:var(--text-muted)">Sản phẩm</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(59,130,246,0.10)">
                <i class="ph ph-arrow-fat-line-down text-lg" style="color:#3b82f6"></i>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(59,130,246,0.10);color:#2563eb">Hôm nay</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold tracking-tight tabular-nums leading-none" style="color:var(--text-primary)">
            {{ $todayIn }}
        </p>
        <p class="text-xs mt-2" style="color:var(--text-muted)">Phiếu nhập</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(16,185,129,0.10)">
                <i class="ph ph-chart-line-up text-lg" style="color:#10b981"></i>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(16,185,129,0.10);color:#059669">VND</span>
        </div>
        <p class="text-2xl lg:text-3xl font-bold tracking-tight tabular-nums leading-none" style="color:var(--text-primary)">
            @php $v = $totalStockValue;
            echo $v >= 1000000000 ? number_format($v/1000000000,1).'B' : number_format($v/1000000,0).'M';
            @endphp
        </p>
        <p class="text-xs mt-2" style="color:var(--text-muted)">Giá trị tồn kho</p>
    </div>

</div>

{{-- ── Charts + Low Stock ─────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Bar chart --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="font-semibold text-sm" style="color:var(--text-primary)">Phiếu nhập / xuất</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">6 tháng gần nhất</p>
            </div>
            <div class="flex items-center gap-3 pt-0.5">
                <span class="inline-flex items-center gap-1.5 text-xs" style="color:var(--text-muted)">
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>Nhập
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs" style="color:var(--text-muted)">
                    <span class="inline-block w-2 h-2 rounded-full bg-orange-400 flex-shrink-0"></span>Xuất
                </span>
            </div>
        </div>
        <div class="flex-1 min-h-0" style="position:relative;height:190px">
            <canvas id="txChart"></canvas>
        </div>
    </div>

    {{-- Line chart --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex flex-col"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="mb-5">
            <p class="font-semibold text-sm" style="color:var(--text-primary)">Giá trị hàng hóa theo kho</p>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">6 tháng — Kho Tổng: nhập · Kho 43/44: xuất</p>
        </div>
        <div class="flex-1 min-h-0" style="position:relative;height:190px">
            <canvas id="warehouseChart"></canvas>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <i class="ph ph-warning text-sm" style="color:#f59e0b"></i>
                <h2 class="font-semibold text-sm" style="color:var(--text-primary)">Sắp hết hàng</h2>
            </div>
            @if($lowStockItems->count())
            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                  style="background:rgba(239,68,68,0.10);color:#dc2626">
                {{ $lowStockItems->count() }}
            </span>
            @endif
        </div>
        <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700" style="max-height:262px">
            @forelse($lowStockItems as $item)
            @php
                $isEmpty  = $item->quantity <= 0;
                $clr      = $isEmpty ? '#ef4444' : '#f97316';
                $minStock = $item->product->min_stock ?? 1;
                $pct      = $minStock > 0 ? min(100, max(0, ($item->quantity / $minStock) * 100)) : 0;
            @endphp
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-1.5 h-1.5 rounded-full flex-shrink-0 mt-0.5" style="background:{{ $clr }}"></div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium" style="color:var(--text-primary)">{{ $item->product->name ?? '—' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="h-1 rounded-full overflow-hidden" style="width:52px;background:rgba(0,0,0,0.06)">
                            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:{{ $clr }}"></div>
                        </div>
                        <span class="text-[10px] font-mono" style="color:var(--text-muted)">{{ $item->product->sku ?? '' }}</span>
                    </div>
                </div>
                <div class="flex-shrink-0 text-right">
                    <span class="text-sm font-bold tabular-nums" style="color:{{ $clr }}">{{ number_format($item->quantity, 0, ',', '.') }}</span>
                    <span class="text-[10px] block" style="color:var(--text-muted)">/ {{ number_format($minStock, 0, ',', '.') }}</span>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <i class="ph ph-check-circle text-3xl mb-2" style="color:#10b981"></i>
                <p class="text-xs" style="color:var(--text-muted)">Tồn kho ổn định</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ── Recent Transactions ────────────────────────────────── --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-2">
            <i class="ph ph-receipt text-sm" style="color:var(--text-muted)"></i>
            <h2 class="font-semibold text-sm" style="color:var(--text-primary)">Phiếu gần đây</h2>
        </div>
        <a href="{{ route('transactions.index') }}"
           class="text-xs font-medium hover:underline" style="color:var(--sidebar-accent)">
            Xem tất cả →
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Số phiếu</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Loại</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ngày</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đối tác</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $tx)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('transactions.show', $tx) }}"
                           class="font-mono text-xs font-semibold hover:underline"
                           style="color:var(--sidebar-accent)">
                            {{ $tx->code }}
                        </a>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($tx->type === 'IN')
                        <span class="badge-green inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                            <i class="bi bi-download text-[10px]"></i> Nhập
                        </span>
                        @else
                        <span class="badge-orange inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                            <i class="bi bi-upload text-[10px]"></i> Xuất
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-secondary)">{{ $tx->date?->format('d/m/Y') }}</td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-primary)">
                        {{ $tx->supplier?->name ?? $tx->destination?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @php
                        $badgeClass = match($tx->status) {
                            'draft'    => 'badge-gray',
                            'pending'  => 'badge-yellow',
                            'approved' => 'badge-green',
                            'rejected' => 'badge-red',
                            default    => 'badge-gray',
                        };
                        $label = match($tx->status) {
                            'draft'    => 'Nháp',
                            'pending'  => 'Chờ duyệt',
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Từ chối',
                            default    => $tx->status,
                        };
                        @endphp
                        <span class="{{ $badgeClass }} inline-flex text-xs font-medium px-2 py-0.5 rounded-full">{{ $label }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-14 text-center">
                        <i class="ph ph-receipt text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Chưa có phiếu nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const isDark    = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.05)';
    const textColor = isDark ? '#6b7280' : '#9ca3af';

    Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";

    // ── Bar chart ───────────────────────────────────────────
    new Chart(document.getElementById('txChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                { label: 'Nhập kho', data: @json($chartIn),  backgroundColor: '#3b82f6', borderRadius: 5, barPercentage: 0.55, categoryPercentage: 0.7 },
                { label: 'Xuất kho', data: @json($chartOut), backgroundColor: '#fb923c', borderRadius: 5, barPercentage: 0.55, categoryPercentage: 0.7 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                y: { grid: { color: gridColor }, border: { display: false }, ticks: { color: textColor, font: { size: 11 }, stepSize: 1 }, beginAtZero: true }
            }
        }
    });

    // ── Line chart ──────────────────────────────────────────
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
                    labels: { color: textColor, font: { size: 11 }, boxWidth: 8, boxHeight: 8, padding: 14, usePointStyle: true, pointStyle: 'circle' }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + ctx.dataset.label + ': ' + fmtVND(ctx.parsed.y) + 'đ'
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                y: {
                    grid: { color: gridColor }, border: { display: false },
                    ticks: { color: textColor, font: { size: 11 }, callback: (v) => fmtVND(v) },
                    beginAtZero: true
                }
            }
        }
    });
})();
</script>
@endpush
