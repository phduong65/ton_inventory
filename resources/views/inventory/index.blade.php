@extends('layouts.admin')

@section('title', 'Tồn kho')
@section('page-title', 'Tồn kho')
@section('breadcrumb', 'Kho / Tồn kho')

@section('content')

{{-- ── Warehouse Tabs ────────────────────────────────────── --}}
<div class="flex items-center gap-1 mb-5 p-1 rounded-xl w-fit flex-wrap"
     style="background:var(--surface-card);border:1px solid var(--surface-border)">
    <a href="{{ route('inventory.index', request()->except(['destination_id','page','as_of','product_id'])) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150"
       style="{{ !request('destination_id') ? 'background:#4f46e5;color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-building text-sm"></i> Kho Tổng (40)
    </a>
    @foreach($destinations as $dest)
    <a href="{{ route('inventory.index', ['destination_id' => $dest->id]) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150"
       style="{{ request('destination_id') == $dest->id ? 'background:#4f46e5;color:#fff' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-box-seam text-sm"></i> {{ $dest->name }}
    </a>
    @endforeach
</div>

{{-- ════════════════════════════════════════════════════════════════
     CHẾ ĐỘ KHO TỔNG (40)
═════════════════════════════════════════════════════════════════ --}}
@if(!request('destination_id'))

{{-- Summary + Export --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl border"
             style="background:rgba(16,185,129,0.06);border-color:rgba(16,185,129,0.2)">
            <i class="ph ph-currency-circle-dollar text-sm" style="color:#10b981"></i>
            <span class="text-sm font-medium" style="color:var(--text-secondary)">
                Tổng giá trị: <strong style="color:#059669">{{ number_format($totalValue, 0, ',', '.') }}đ</strong>
            </span>
        </div>
        @if($lowStockCount > 0)
        <a href="{{ route('inventory.index', array_merge(request()->query(), ['low_stock' => 1])) }}"
           class="flex items-center gap-1.5 px-3 py-2 rounded-xl border transition-colors"
           style="background:rgba(239,68,68,0.06);border-color:rgba(239,68,68,0.2);color:#dc2626">
            <i class="ph ph-warning text-sm"></i>
            <span class="text-sm font-medium">{{ $lowStockCount }} sản phẩm dưới ngưỡng</span>
        </a>
        @endif
    </div>
    @can('export-inventory')
    <a href="{{ route('inventory.export', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
       style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
        <i class="ph ph-file-xls text-base"></i> Xuất Excel
    </a>
    @endcan
</div>

{{-- Table Card --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-center" style="border-bottom:1px solid var(--surface-border)">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm SKU, tên..."
               class="form-input flex-1 min-w-[160px] h-9 text-sm">
        <select name="category_id" class="form-input flex-1 min-w-[140px] h-9 text-sm">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-xs font-medium cursor-pointer select-none" style="color:var(--text-secondary)">
            <input type="checkbox" name="has_stock" value="1" {{ request('has_stock') ? 'checked' : '' }} class="rounded">
            Chỉ có tồn
        </label>
        <label class="flex items-center gap-2 text-xs font-medium cursor-pointer select-none text-red-600 dark:text-red-400">
            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded">
            <i class="ph ph-warning text-xs"></i> Dưới ngưỡng
        </label>
        <button type="submit" class="h-9 px-4 text-sm font-medium rounded-xl transition-colors"
                style="background:var(--surface-bg);border:1px solid var(--surface-border);color:var(--text-secondary)"
                onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
            <i class="ph ph-magnifying-glass mr-1"></i> Lọc
        </button>
        @if(request()->hasAny(['search','category_id','has_stock','low_stock']))
        <a href="{{ route('inventory.index') }}"
           class="h-9 px-3 inline-flex items-center rounded-xl border text-sm transition-colors"
           style="border-color:var(--surface-border);color:var(--text-muted)">
            <i class="ph ph-x text-sm"></i>
        </a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SKU</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tên sản phẩm</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Danh mục</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SL tồn</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ngưỡng</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá vốn</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá trị tồn</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                @php $belowMin = $item->product?->isBelowMinStock(); @endphp
                <tr class="border-t transition-colors {{ $belowMin ? 'border-red-100 dark:border-red-900/30 bg-red-50/40 dark:bg-red-900/5 hover:bg-red-50 dark:hover:bg-red-900/10' : 'border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025]' }}">
                    <td class="px-5 py-3.5 font-mono text-xs" style="color:var(--text-muted)">{{ $item->product?->sku }}</td>
                    <td class="px-5 py-3.5 font-medium text-xs whitespace-normal break-words max-w-[220px] {{ $belowMin ? 'text-red-700 dark:text-red-400' : '' }}" style="{{ $belowMin ? '' : 'color:var(--text-primary)' }}">
                        <div class="flex items-center gap-1.5">
                            {{ $item->product?->name }}
                            @if($belowMin)
                            <i class="ph ph-warning-circle text-xs text-red-500"></i>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $item->product?->category?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $item->product?->unit?->name }}</td>
                    <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-xs
                        {{ $belowMin ? 'text-red-600 dark:text-red-400' : ($item->quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}"
                        style="{{ !$belowMin && $item->quantity <= 0 ? 'color:var(--text-muted)' : '' }}">
                        {{ number_format($item->quantity, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs tabular-nums {{ $belowMin ? 'text-red-500 dark:text-red-400 font-medium' : '' }}" style="{{ !$belowMin ? 'color:var(--text-muted)' : '' }}">
                        @if($item->product?->min_stock > 0)
                        {{ number_format($item->product->min_stock, 0, ',', '.') }}
                        @else
                        <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs tabular-nums" style="color:var(--text-muted)">
                        {{ number_format($item->average_cost, 0, ',', '.') }}đ
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs font-medium tabular-nums" style="color:var(--text-primary)">
                        {{ number_format($item->quantity * $item->average_cost, 0, ',', '.') }}đ
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('stock-ledger.index', ['product_id' => $item->product_id]) }}"
                           class="text-xs font-medium hover:underline" style="color:var(--sidebar-accent)">
                            Thẻ kho
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-16 text-center">
                        <i class="ph ph-archive text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-xs" style="color:var(--text-muted)">Không có dữ liệu tồn kho</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
    <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
        {{ $items->links() }}
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════
     CHẾ ĐỘ KHO CON (có destination_id)
═════════════════════════════════════════════════════════════════ --}}
@else

{{-- Toolbar --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl border"
             style="background:rgba(59,130,246,0.06);border-color:rgba(59,130,246,0.2)">
            <i class="ph ph-warehouse text-sm" style="color:#3b82f6"></i>
            <span class="text-sm font-medium" style="color:var(--text-secondary)">
                {{ $activeDestination?->name }}:
                <strong style="color:#2563eb">{{ number_format(($destRows ?? collect())->sum('value'), 0, ',', '.') }}đ</strong>
            </span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs"
             style="background:var(--surface-bg);border:1px solid var(--surface-border);color:var(--text-muted)">
            <i class="ph ph-calendar-blank text-sm"></i>
            Lũy kế đến <strong class="ml-1" style="color:var(--text-secondary)">{{ \Carbon\Carbon::parse($asOf ?? now())->format('d/m/Y') }}</strong>
        </div>
    </div>
    @can('export-reports')
    <a href="{{ route('inventory.export', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
       style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
        <i class="ph ph-file-xls text-base"></i> Xuất Excel
    </a>
    @endcan
</div>

{{-- Filter --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 mb-4 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
        <input type="hidden" name="destination_id" value="{{ request('destination_id') }}">
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn tại ngày</label>
            <input type="date" name="as_of" value="{{ $asOf ?? now()->format('Y-m-d') }}"
                   max="{{ now()->format('Y-m-d') }}"
                   class="form-input h-9 text-sm w-40">
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
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</label>
            <input type="hidden" name="product_id" :value="currentValue">
            <button type="button" class="sp-trigger w-56" @click="openPalette()">
                <span x-text="currentLabel || 'Tất cả sản phẩm'"></span>
                <i class="bi bi-search"></i>
            </button>
            @include('partials.select-palette', ['placeholder' => 'Tìm theo tên, SKU...', 'countLabel' => 'sản phẩm'])
        </div>
        <button type="submit" class="h-9 px-4 text-sm font-medium text-white rounded-xl self-end transition-colors"
                style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            <i class="ph ph-magnifying-glass mr-1"></i> Xem
        </button>
        @if(request()->hasAny(['as_of','product_id']))
        <a href="{{ route('inventory.index', ['destination_id' => request('destination_id')]) }}"
           class="h-9 px-3 inline-flex items-center rounded-xl border text-sm self-end transition-colors"
           style="border-color:var(--surface-border);color:var(--text-muted)">
            <i class="ph ph-x text-sm"></i>
        </a>
        @endif
    </form>
</div>

{{-- Destination Table --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
    @if(($destRows ?? collect())->isEmpty())
    <div class="px-5 py-16 text-center">
        <i class="ph ph-warehouse text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
        <p class="text-sm font-medium mb-1" style="color:var(--text-secondary)">Không có dữ liệu tồn kho</p>
        <p class="text-xs" style="color:var(--text-muted)">Chưa có phiếu xuất kho được duyệt đến ngày đã chọn.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SKU</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tên sản phẩm</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Danh mục</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SL lũy kế</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá vốn TB</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá trị</th>
                </tr>
            </thead>
            <tbody>
                @foreach($destRows as $row)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-3.5 font-mono text-xs" style="color:var(--text-muted)">{{ $row->product?->sku ?? '—' }}</td>
                    <td class="px-5 py-3.5 font-medium text-xs whitespace-normal break-words max-w-[220px]" style="color:var(--text-primary)">{{ $row->product?->name }}</td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $row->product?->category?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $row->product?->unit?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-xs" style="color:var(--text-primary)">
                        {{ number_format($row->qty, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs tabular-nums" style="color:var(--text-muted)">
                        {{ number_format($row->avg_cost, 0, ',', '.') }}đ
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs font-medium tabular-nums" style="color:var(--text-primary)">
                        {{ number_format($row->value, 0, ',', '.') }}đ
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:var(--surface-bg);border-top:2px solid var(--surface-border)">
                    <td colspan="4" class="px-5 py-3 text-xs font-semibold" style="color:var(--text-secondary)">
                        Tổng — {{ ($destRows ?? collect())->count() }} mặt hàng
                    </td>
                    <td class="px-5 py-3 text-right text-xs font-bold tabular-nums" style="color:var(--text-primary)">
                        {{ number_format(($destRows ?? collect())->sum('qty'), 0, ',', '.') }}
                    </td>
                    <td></td>
                    <td class="px-5 py-3 text-right text-xs font-bold tabular-nums" style="color:#2563eb">
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
