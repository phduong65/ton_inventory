@extends('layouts.admin')

@section('title', 'Tồn kho')
@section('page-title', 'Tồn kho')
@section('breadcrumb', 'Kho / Tồn kho')

@section('content')

{{-- ── Warehouse Tabs ──────────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-4 p-1 rounded-xl w-fit flex-wrap"
     style="background:var(--surface-card); border:1px solid var(--surface-border)">

    {{-- Kho Tổng (40) --}}
    <a href="{{ route('inventory.index', request()->except(['destination_id','page','as_of','product_id'])) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
              {{ !request('destination_id') ? 'text-white shadow-sm' : 'hover:opacity-80' }}"
       style="{{ !request('destination_id') ? 'background:#4f46e5; color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-building text-sm"></i>
        <span>Kho Tổng (40)</span>
    </a>

    {{-- Tabs kho con --}}
    @foreach($destinations as $dest)
    <a href="{{ route('inventory.index', ['destination_id' => $dest->id]) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
              {{ request('destination_id') == $dest->id ? 'text-white shadow-sm' : 'hover:opacity-80' }}"
       style="{{ request('destination_id') == $dest->id ? 'background:#4f46e5; color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-box-seam text-sm"></i>
        <span>{{ $dest->name }}</span>
    </a>
    @endforeach
</div>

{{-- ════════════════════════════════════════════════════════════════
     CHẾ ĐỘ KHO TỔNG (40)
═════════════════════════════════════════════════════════════════ --}}
@if(!request('destination_id'))

<div class="mb-4 flex items-center justify-between gap-3 flex-wrap">
    <div class="flex items-center gap-3">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg px-4 py-2">
            <span class="text-sm text-green-700 dark:text-green-400 font-medium">
                Tổng giá trị tồn kho: <strong>{{ number_format($totalValue, 0, ',', '.') }}đ</strong>
            </span>
        </div>
        @if($lowStockCount > 0)
        <a href="{{ route('inventory.index', array_merge(request()->query(), ['low_stock' => 1])) }}"
           class="flex items-center gap-1.5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2 text-sm text-red-700 dark:text-red-400 font-medium hover:bg-red-100 dark:hover:bg-red-900/40">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $lowStockCount }} sản phẩm dưới ngưỡng
        </a>
        @endif
    </div>
    @can('export-inventory')
    <a href="{{ route('inventory.export', request()->query()) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
        <i class="bi bi-file-earmark-excel"></i> Xuất Excel
    </a>
    @endcan
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm SKU, tên..."
               class="flex-1 min-w-[160px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        <select name="category_id" class="flex-1 min-w-[140px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="checkbox" name="has_stock" value="1" {{ request('has_stock') ? 'checked' : '' }}> Chỉ có tồn
        </label>
        <label class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400 cursor-pointer font-medium">
            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}>
            <i class="bi bi-exclamation-triangle-fill text-xs"></i> Dưới ngưỡng
        </label>
        <button type="submit" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
            <i class="bi bi-search mr-1"></i> Lọc
        </button>
        @if(request()->hasAny(['search', 'category_id', 'has_stock', 'low_stock']))
        <a href="{{ route('inventory.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            Xóa lọc
        </a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Tên sản phẩm</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">SL tồn</th>
                    <th class="px-4 py-3 text-right">Ngưỡng</th>
                    <th class="px-4 py-3 text-right">Giá vốn</th>
                    <th class="px-4 py-3 text-right">Giá trị tồn</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($items as $item)
                @php $belowMin = $item->product?->isBelowMinStock(); @endphp
                <tr class="{{ $belowMin ? 'bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->product?->sku }}</td>
                    <td class="px-4 py-3 font-medium {{ $belowMin ? 'text-red-700 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $item->product?->name }}
                        @if($belowMin)
                        <span class="ml-1.5 inline-flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
                            <i class="bi bi-exclamation-triangle-fill text-[10px]"></i> Dưới ngưỡng
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $item->product?->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $item->product?->unit?->name }}</td>
                    <td class="px-4 py-3 text-right font-semibold tabular-nums
                        {{ $belowMin ? 'text-red-600 dark:text-red-400' : ($item->quantity > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400') }}">
                        {{ number_format($item->quantity, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums
                        {{ $belowMin ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-400' }}">
                        @if($item->product?->min_stock > 0)
                            {{ number_format($item->product->min_stock, 0, ',', '.') }}
                        @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ number_format($item->average_cost, 0, ',', '.') }}đ</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                        {{ number_format($item->quantity * $item->average_cost, 0, ',', '.') }}đ
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('stock-ledger.index', ['product_id' => $item->product_id]) }}"
                           class="text-xs text-primary-600 hover:underline">Thẻ kho</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph ph-archive text-4xl block mb-2"></i>
                        Không có dữ liệu tồn kho
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $items->links() }}
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════
     CHẾ ĐỘ KHO CON (có destination_id)
═════════════════════════════════════════════════════════════════ --}}
@else

{{-- Toolbar --}}
<div class="mb-4 flex items-center justify-between gap-3 flex-wrap">
    <div class="flex items-center gap-3">
        {{-- Tổng giá trị --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-2">
            <span class="text-sm text-blue-700 dark:text-blue-400 font-medium">
                {{ $activeDestination?->name }}:
                <strong>{{ number_format(($destRows ?? collect())->sum('value'), 0, ',', '.') }}đ</strong>
            </span>
        </div>
        {{-- As-of date badge --}}
        <span class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-xs text-gray-500">
            <i class="bi bi-calendar3"></i>
            Lũy kế đến <strong class="text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($asOf ?? now())->format('d/m/Y') }}</strong>
        </span>
    </div>
    <div class="flex items-center gap-2">
        @can('export-reports')
        <a href="{{ route('inventory.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
            <i class="bi bi-file-earmark-excel"></i> Xuất Excel
        </a>
        @endcan
    </div>
</div>

{{-- Filter bar --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-4">
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
        <input type="hidden" name="destination_id" value="{{ request('destination_id') }}">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Tồn tại ngày</label>
            <input type="date" name="as_of" value="{{ $asOf ?? now()->format('Y-m-d') }}"
                   max="{{ now()->format('Y-m-d') }}"
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-40">
        </div>
        @php
            $productItems = array_merge(
                [['v' => '', 'l' => 'Tất cả sản phẩm']],
                ($products ?? collect())->map(fn($p) => ['v' => $p->id, 'l' => $p->name, 's' => $p->sku ?? ''])->toArray()
            );
        @endphp
        <div class="flex flex-col gap-1"
             x-data="selectPalette({ value: '{{ request('product_id') ?? '' }}', items: {{ Js::from($productItems) }} })"
             @keydown.escape.window="if(open){ close(); $event.stopPropagation(); }"
             @keydown.arrow-down.window.prevent="if(open) moveDown()"
             @keydown.arrow-up.window.prevent="if(open) moveUp()"
             @keydown.enter.window.prevent="if(open) confirm()">
            <label class="text-xs text-gray-500 dark:text-gray-400">Sản phẩm</label>
            <input type="hidden" name="product_id" :value="currentValue">
            <button type="button" class="sp-trigger w-56" @click="openPalette()">
                <span x-text="currentLabel || 'Tất cả sản phẩm'"></span>
                <i class="bi bi-search"></i>
            </button>
            @include('partials.select-palette', ['placeholder' => 'Tìm theo tên, SKU...', 'countLabel' => 'sản phẩm'])
        </div>
        <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg self-end">
            <i class="bi bi-search mr-1"></i> Xem
        </button>
        @if(request()->hasAny(['as_of', 'product_id']))
        <a href="{{ route('inventory.index', ['destination_id' => request('destination_id')]) }}"
           class="px-3 py-2 text-sm bg-red-500 text-white hover:text-red-700 dark:hover:text-gray-300 self-end rounded-lg flex items-center gap-1">
            Xóa lọc
        </a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if(($destRows ?? collect())->isEmpty())
    <div class="px-4 py-16 text-center text-gray-400">
        <i class="ph ph-warehouse text-5xl block mb-3"></i>
        <p class="text-base font-medium text-gray-500 dark:text-gray-400">Không có dữ liệu tồn kho</p>
        <p class="text-sm mt-1">Chưa có phiếu xuất kho được duyệt đến ngày đã chọn.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Tên sản phẩm</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">SL lũy kế</th>
                    <th class="px-4 py-3 text-right">Giá vốn TB</th>
                    <th class="px-4 py-3 text-right">Giá trị</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($destRows as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $row->product?->sku ?? '—' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row->product?->name }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row->product?->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row->product?->unit?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-gray-900 dark:text-white">
                        {{ number_format($row->qty, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500 tabular-nums">
                        {{ number_format($row->avg_cost, 0, ',', '.') }}đ
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white tabular-nums">
                        {{ number_format($row->value, 0, ',', '.') }}đ
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-700/40 text-sm font-semibold text-gray-700 dark:text-gray-300">
                <tr>
                    <td colspan="4" class="px-4 py-3">
                        Tổng — {{ ($destRows ?? collect())->count() }} mặt hàng
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        {{ number_format(($destRows ?? collect())->sum('qty'), 0, ',', '.') }}
                    </td>
                    <td></td>
                    <td class="px-4 py-3 text-right text-blue-600 dark:text-blue-400 tabular-nums">
                        {{ number_format(($destRows ?? collect())->sum('value'), 0, ',', '.') }}đ
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

@endif

@endsection
