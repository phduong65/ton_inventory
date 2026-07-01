@extends('layouts.admin')

@section('title', 'Công nợ nội bộ')
@section('page-title', 'Báo cáo công nợ nội bộ')
@section('breadcrumb', 'Báo cáo / Công nợ nội bộ')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end" style="border-bottom:1px solid var(--surface-border)">
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tháng</label>
            <select name="month" class="form-input h-9 text-sm w-32">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                @endfor
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Năm</label>
            <select name="year" class="form-input h-9 text-sm w-28">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="flex gap-2 self-end">
            <button type="submit" class="h-9 px-4 text-sm font-medium text-white rounded-xl transition-colors"
                    style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                <i class="ph ph-magnifying-glass mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('reports.internal-debt.export', ['month' => $month, 'year' => $year]) }}"
               class="h-9 px-4 inline-flex items-center gap-1.5 text-sm font-medium text-white rounded-xl transition-colors"
               style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <i class="ph ph-file-xls text-base"></i> Xuất Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Period label + grand total --}}
    <div class="px-5 py-2.5 flex items-center justify-between text-sm" style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
        <span style="color:var(--text-muted)">
            Kỳ đối soát: <strong style="color:var(--text-primary)">Tháng {{ $month }}/{{ $year }}</strong>
        </span>
        <span class="font-semibold" style="color:var(--text-secondary)">
            Tổng giá trị xuất: <span style="color:#ef4444">{{ number_format($grandTotal, 0, ',', '.') }}đ</span>
        </span>
    </div>

    {{-- Destination totals summary cards --}}
    @if($destinations->count())
    <div class="px-5 py-4 flex flex-wrap gap-4" style="border-bottom:1px solid var(--surface-border)">
        @foreach($destinations as $destId => $dest)
        <div class="rounded-xl border px-4 py-3 flex flex-col gap-1 min-w-[160px]"
             style="background:rgba(59,130,246,0.04);border-color:rgba(59,130,246,0.15)">
            <span class="text-xs font-semibold" style="color:#3b82f6">{{ $dest->name }}</span>
            <span class="text-lg font-bold tabular-nums" style="color:var(--text-primary)">
                {{ number_format($destTotals[$destId]['value'] ?? 0, 0, ',', '.') }}đ
            </span>
            <span class="text-xs" style="color:var(--text-muted)">
                SL: {{ number_format($destTotals[$destId]['qty'] ?? 0, 0, ',', '.') }}
            </span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr style="background:var(--surface-bg)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide border-b border-r" style="color:var(--text-muted);border-color:var(--surface-border)" rowspan="2">Sản phẩm</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide border-b border-r" style="color:var(--text-muted);border-color:var(--surface-border)" rowspan="2">ĐVT</th>
                    @foreach($destinations as $dest)
                    <th class="px-5 py-2 text-center text-[11px] font-semibold uppercase tracking-wide border-b border-l" style="color:var(--text-muted);border-color:var(--surface-border)" colspan="2">{{ $dest->name }}</th>
                    @endforeach
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide border-b border-l" style="color:var(--text-muted);border-color:var(--surface-border)" rowspan="2">Tổng giá trị</th>
                </tr>
                <tr style="background:var(--surface-bg)">
                    @foreach($destinations as $dest)
                    <th class="px-4 py-2 text-right text-[11px] font-normal border-b border-l" style="color:var(--text-muted);border-color:var(--surface-border)">SL</th>
                    <th class="px-4 py-2 text-right text-[11px] font-normal border-b" style="color:var(--text-muted);border-color:var(--surface-border)">Giá trị</th>
                    @endforeach
                    <th class="border-b border-l" style="border-color:var(--surface-border)"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportRows as $row)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-2.5">
                        <div class="font-medium text-sm" style="color:var(--text-primary)">{{ $row['product']?->name }}</div>
                        <div class="text-[10px] font-mono" style="color:var(--text-muted)">{{ $row['product']?->sku }}</div>
                    </td>
                    <td class="px-5 py-2.5 text-center text-xs" style="color:var(--text-muted)">{{ $row['product']?->unit?->name ?? '—' }}</td>
                    @foreach($destinations as $destId => $dest)
                    @php $d = $row['destData'][$destId] ?? ['qty' => 0, 'value' => 0]; @endphp
                    <td class="px-4 py-2.5 text-right text-xs tabular-nums border-l" style="border-color:var(--surface-border);{{ $d['qty'] > 0 ? 'color:var(--text-primary)' : 'color:var(--text-muted);opacity:0.4' }}">
                        {{ $d['qty'] > 0 ? number_format($d['qty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-xs tabular-nums" style="{{ $d['value'] > 0 ? 'color:var(--text-secondary)' : 'color:var(--text-muted);opacity:0.4' }}">
                        {{ $d['value'] > 0 ? number_format($d['value'], 0, ',', '.') : '—' }}
                    </td>
                    @endforeach
                    <td class="px-5 py-2.5 text-right font-semibold text-sm tabular-nums border-l" style="border-color:var(--surface-border);color:var(--text-primary)">
                        {{ number_format($row['totalValue'], 0, ',', '.') }}đ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 2 + $destinations->count() * 2 + 1 }}" class="px-5 py-16 text-center">
                        <i class="ph ph-scales text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Không có dữ liệu xuất kho trong tháng {{ $month }}/{{ $year }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($reportRows->count())
            <tfoot>
                <tr style="background:var(--surface-bg);border-top:2px solid var(--surface-border)">
                    <td colspan="2" class="px-5 py-3 text-sm font-semibold" style="color:var(--text-secondary)">Tổng cộng</td>
                    @foreach($destinations as $destId => $dest)
                    @php $dt = $destTotals[$destId] ?? ['qty' => 0, 'value' => 0]; @endphp
                    <td class="px-4 py-3 text-right text-sm font-bold tabular-nums border-l" style="border-color:var(--surface-border);color:var(--text-primary)">{{ number_format($dt['qty'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-sm font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($dt['value'], 0, ',', '.') }}đ</td>
                    @endforeach
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums border-l" style="border-color:var(--surface-border);color:#ef4444">
                        {{ number_format($grandTotal, 0, ',', '.') }}đ
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
