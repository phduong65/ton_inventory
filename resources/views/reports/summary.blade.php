@extends('layouts.admin')

@section('title', 'Báo cáo nhập xuất tồn')
@section('page-title', 'Báo cáo nhập xuất tồn')
@section('breadcrumb', 'Báo cáo / Nhập xuất tồn')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end" style="border-bottom:1px solid var(--surface-border)">
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Từ ngày</label>
            <x-date-picker name="date_from" :value="$from" class="w-36" placeholder="Từ ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đến ngày</label>
            <x-date-picker name="date_to" :value="$to" class="w-36" placeholder="Đến ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Kỳ nhanh</label>
            <div class="flex gap-1 flex-wrap">
                @php
                $quickRanges = [
                    'Tháng này'   => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
                    'Tháng trước' => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
                    'Quý này'     => [now()->startOfQuarter()->format('Y-m-d'), now()->format('Y-m-d')],
                    'Năm nay'     => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
                ];
                @endphp
                @foreach($quickRanges as $label => [$qFrom, $qTo])
                <a href="{{ route('reports.summary', ['date_from' => $qFrom, 'date_to' => $qTo]) }}"
                   class="h-9 px-3 inline-flex items-center text-xs font-medium rounded-xl border transition-colors"
                   style="{{ $from === $qFrom && $to === $qTo ? 'background:#4f46e5;color:#fff;border-color:#4f46e5' : 'border-color:var(--surface-border);color:var(--text-secondary)' }}"
                   @if(!($from === $qFrom && $to === $qTo))
                   onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'"
                   @endif>
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
        <div class="flex gap-2 self-end">
            <button type="submit" class="h-9 px-4 text-sm font-medium text-white rounded-xl transition-colors"
                    style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                <i class="ph ph-magnifying-glass mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('reports.summary.export', request()->query()) }}"
               class="h-9 px-4 inline-flex items-center gap-1.5 text-sm font-medium text-white rounded-xl transition-colors"
               style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <i class="ph ph-file-xls text-base"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Period header --}}
    <div class="px-5 py-2.5 flex items-center gap-3 text-xs" style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
        <i class="ph ph-calendar-range text-sm" style="color:var(--text-muted)"></i>
        <span style="color:var(--text-muted)">Kỳ báo cáo:</span>
        <strong style="color:var(--text-primary)">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</strong>
        <span style="color:var(--text-muted)">—</span>
        <strong style="color:var(--text-primary)">{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</strong>
        <span class="px-2 py-0.5 rounded-full font-semibold" style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $rows->count() }} SP</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SKU</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn đầu kỳ</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">Nhập</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-red-500 dark:text-red-400">Xuất</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">Điều chỉnh</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn cuối kỳ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-2.5 font-mono text-xs" style="color:var(--text-muted)">{{ $row['product']?->sku }}</td>
                    <td class="px-5 py-2.5 whitespace-normal break-words max-w-[220px]">
                        <span class="font-medium text-sm" style="color:var(--text-primary)">{{ $row['product']?->name }}</span>
                        <span class="text-xs ml-1" style="color:var(--text-muted)">{{ $row['product']?->category?->name }}</span>
                    </td>
                    <td class="px-5 py-2.5 text-xs" style="color:var(--text-muted)">{{ $row['product']?->unit?->name ?? '—' }}</td>
                    <td class="px-5 py-2.5 text-right text-xs tabular-nums" style="color:var(--text-secondary)">{{ number_format($row['openQty'], 0, ',', '.') }}</td>
                    <td class="px-5 py-2.5 text-right font-medium tabular-nums text-sm {{ $row['inQty'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : '' }}" style="{{ $row['inQty'] <= 0 ? 'color:var(--text-muted)' : '' }}">
                        {{ $row['inQty'] > 0 ? '+'.number_format($row['inQty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-5 py-2.5 text-right font-medium tabular-nums text-sm {{ $row['outQty'] > 0 ? 'text-red-500 dark:text-red-400' : '' }}" style="{{ $row['outQty'] <= 0 ? 'color:var(--text-muted)' : '' }}">
                        {{ $row['outQty'] > 0 ? number_format($row['outQty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-5 py-2.5 text-right tabular-nums text-sm {{ $row['adjQty'] != 0 ? ($row['adjQty'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-orange-600 dark:text-orange-400') : '' }}" style="{{ $row['adjQty'] === 0 ? 'color:var(--text-muted)' : '' }}">
                        {{ $row['adjQty'] != 0 ? ($row['adjQty'] > 0 ? '+' : '').number_format($row['adjQty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-5 py-2.5 text-right font-semibold tabular-nums text-sm" style="{{ $row['closeQty'] > 0 ? 'color:var(--text-primary)' : 'color:var(--text-muted)' }}">
                        {{ number_format($row['closeQty'], 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <i class="ph ph-chart-bar text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Không có dữ liệu trong kỳ này</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot>
                <tr style="background:var(--surface-bg);border-top:2px solid var(--surface-border)">
                    <td colspan="3" class="px-5 py-3 text-sm font-semibold" style="color:var(--text-secondary)">Tổng cộng</td>
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums" style="color:var(--text-secondary)">{{ number_format($rows->sum('openQty'), 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums text-emerald-600 dark:text-emerald-400">+{{ number_format($rows->sum('inQty'), 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums text-red-500 dark:text-red-400">{{ number_format($rows->sum('outQty'), 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums text-amber-600 dark:text-amber-400">{{ number_format($rows->sum('adjQty'), 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($rows->sum('closeQty'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
