@extends('layouts.admin')

@section('title', 'Báo cáo tồn kho')
@section('page-title', 'Báo cáo tồn kho')
@section('breadcrumb', 'Báo cáo / Tồn kho')

@section('content')

{{-- ── Warehouse Tabs ──────────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-4 p-1 rounded-xl w-fit flex-wrap"
     style="background:var(--surface-card); border:1px solid var(--surface-border)">
    <a href="{{ route('reports.inventory', request()->except(['destination_id', 'page'])) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
              {{ !request('destination_id') ? 'text-white shadow-sm' : 'hover:opacity-80' }}"
       style="{{ !request('destination_id') ? 'background:#4f46e5; color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-building text-sm"></i>
        <span>Kho Tổng (40)</span>
    </a>
    @foreach($destinations as $dest)
    <a href="{{ route('reports.inventory', array_merge(request()->except(['destination_id','page']), ['destination_id' => $dest->id])) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
              {{ request('destination_id') == $dest->id ? 'text-white shadow-sm' : 'hover:opacity-80' }}"
       style="{{ request('destination_id') == $dest->id ? 'background:#4f46e5; color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-box-seam text-sm"></i>
        <span>{{ $dest->name }}</span>
    </a>
    @endforeach
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- ── Filter ──────────────────────────────────────────────────── --}}
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-end">
        @if(request('destination_id'))
        <input type="hidden" name="destination_id" value="{{ request('destination_id') }}">
        @endif
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Tồn tại ngày</label>
            <x-date-picker name="as_of" :value="$asOf" max-date="today" class="w-40" placeholder="Chọn ngày" />
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                <i class="bi bi-search mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('inventory.export', request()->query()) }}"
               class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center gap-1">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- ── Info bar ─────────────────────────────────────────────────── --}}
    <div class="px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm">
        <span class="text-green-700 dark:text-green-400 flex items-center gap-1.5">
            <i class="bi bi-calendar3"></i>
            @if(request('destination_id'))
                Lũy kế xuất đến {{ $activeDestination?->name }}
            @elseif($asOf >= $today)
                Tồn kho hiện tại — Kho Tổng (40)
            @else
                Tồn kho tại ngày {{ \Carbon\Carbon::parse($asOf)->format('d/m/Y') }} — Kho Tổng (40)
            @endif
        </span>
        <span class="font-semibold text-green-700 dark:text-green-400">
            Tổng giá trị: {{ number_format($totalValue, 0, ',', '.') }}đ
        </span>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────── --}}
    @php $rows = request('destination_id') ? ($destItems ?? collect()) : ($items ?? collect()); @endphp
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Sản phẩm</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">
                        {{ request('destination_id') ? 'SL lũy kế' : 'SL tồn' }}
                    </th>
                    <th class="px-4 py-3 text-right">Giá vốn TB</th>
                    <th class="px-4 py-3 text-right">Giá trị</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($rows as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $item->product?->sku ?? '—' }}</td>
                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $item->product?->name }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $item->product?->category?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $item->product?->unit?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-right font-medium tabular-nums {{ $item->quantity > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                        {{ number_format($item->quantity, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-gray-500 tabular-nums">{{ number_format($item->average_cost, 0, ',', '.') }}đ</td>
                    <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white tabular-nums">
                        {{ number_format($item->quantity * $item->average_cost, 0, ',', '.') }}đ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph ph-archive text-4xl block mb-2"></i>
                        Không có dữ liệu tồn kho tại thời điểm này
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-300 text-sm">
                <tr>
                    <td colspan="4" class="px-4 py-3">Tổng cộng ({{ number_format($rows->count()) }} sản phẩm)</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($rows->sum('quantity'), 0, ',', '.') }}</td>
                    <td></td>
                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400 tabular-nums">{{ number_format($totalValue, 0, ',', '.') }}đ</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
