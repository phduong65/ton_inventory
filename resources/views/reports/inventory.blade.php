@extends('layouts.admin')

@section('title', 'Báo cáo tồn kho')
@section('page-title', 'Báo cáo tồn kho')
@section('breadcrumb', 'Báo cáo / Tồn kho')

@section('content')

{{-- ── Warehouse Tabs ────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-5 p-1 rounded-xl w-fit flex-wrap"
     style="background:var(--surface-card);border:1px solid var(--surface-border)">
    <a href="{{ route('reports.inventory', request()->except(['destination_id','page'])) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150"
       style="{{ !request('destination_id') ? 'background:#4f46e5;color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-building text-sm"></i> Kho Tổng (40)
    </a>
    @foreach($destinations as $dest)
    <a href="{{ route('reports.inventory', array_merge(request()->except(['destination_id','page']), ['destination_id' => $dest->id])) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150"
       style="{{ request('destination_id') == $dest->id ? 'background:#4f46e5;color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-box-seam text-sm"></i> {{ $dest->name }}
    </a>
    @endforeach
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end" style="border-bottom:1px solid var(--surface-border)">
        @if(request('destination_id'))
        <input type="hidden" name="destination_id" value="{{ request('destination_id') }}">
        @endif
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn tại ngày</label>
            <x-date-picker name="as_of" :value="$asOf" max-date="today" class="w-40" placeholder="Chọn ngày" />
        </div>
        <div class="flex gap-2 self-end">
            <button type="submit" class="h-9 px-4 text-sm font-medium text-white rounded-xl transition-colors"
                    style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                <i class="ph ph-magnifying-glass mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('inventory.export', request()->query()) }}"
               class="h-9 px-4 inline-flex items-center gap-1.5 text-sm font-medium text-white rounded-xl transition-colors"
               style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <i class="ph ph-file-xls text-xs"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Info bar --}}
    <div class="px-5 py-2.5 flex items-center justify-between text-xs" style="background:rgba(16,185,129,0.06);border-bottom:1px solid var(--surface-border)">
        <div class="flex items-center gap-2" style="color:#059669">
            <i class="ph ph-calendar-blank text-xs"></i>
            @if(request('destination_id'))
            <span>Lũy kế xuất đến {{ $activeDestination?->name }}</span>
            @elseif($asOf >= $today)
            <span>Tồn kho hiện tại — Kho Tổng (40)</span>
            @else
            <span>Tồn kho tại ngày {{ \Carbon\Carbon::parse($asOf)->format('d/m/Y') }} — Kho Tổng (40)</span>
            @endif
        </div>
        <span class="font-semibold" style="color:#059669">Tổng giá trị: {{ number_format($totalValue, 0, ',', '.') }}đ</span>
    </div>

    {{-- Table --}}
    @php $rows = request('destination_id') ? ($destItems ?? collect()) : ($items ?? collect()); @endphp
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SKU</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Danh mục</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">{{ request('destination_id') ? 'SL lũy kế' : 'SL tồn' }}</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá vốn TB</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá trị</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $item)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-2.5 font-mono text-xs" style="color:var(--text-muted)">{{ $item->product?->sku ?? '—' }}</td>
                    <td class="px-5 py-2.5 font-medium text-xs whitespace-normal break-words max-w-[220px]" style="color:var(--text-primary)">{{ $item->product?->name }}</td>
                    <td class="px-5 py-2.5 text-xs" style="color:var(--text-muted)">{{ $item->product?->category?->name ?? '—' }}</td>
                    <td class="px-5 py-2.5 text-xs" style="color:var(--text-muted)">{{ $item->product?->unit?->name ?? '—' }}</td>
                    <td class="px-5 py-2.5 text-right font-medium tabular-nums text-xs" style="{{ $item->quantity > 0 ? 'color:var(--text-primary)' : 'color:var(--text-muted)' }}">
                        {{ number_format($item->quantity, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-2.5 text-right text-xs tabular-nums" style="color:var(--text-muted)">{{ number_format($item->average_cost, 0, ',', '.') }}đ</td>
                    <td class="px-5 py-2.5 text-right text-xs font-medium tabular-nums" style="color:var(--text-primary)">
                        {{ number_format($item->quantity * $item->average_cost, 0, ',', '.') }}đ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <i class="ph ph-archive text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-xs" style="color:var(--text-muted)">Không có dữ liệu tồn kho tại thời điểm này</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot>
                <tr style="background:var(--surface-bg);border-top:2px solid var(--surface-border)">
                    <td colspan="4" class="px-5 py-3 text-xs font-semibold" style="color:var(--text-secondary)">
                        Tổng cộng ({{ number_format($rows->count()) }} sản phẩm)
                    </td>
                    <td class="px-5 py-3 text-right text-xs font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($rows->sum('quantity'), 0, ',', '.') }}</td>
                    <td></td>
                    <td class="px-5 py-3 text-right text-xs font-bold tabular-nums" style="color:#16a34a">{{ number_format($totalValue, 0, ',', '.') }}đ</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
