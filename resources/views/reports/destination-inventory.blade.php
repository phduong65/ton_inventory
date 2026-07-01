@extends('layouts.admin')

@section('title', 'Tồn kho Kho con')
@section('page-title', 'Tồn kho Kho con')
@section('breadcrumb', 'Báo cáo / Tồn kho Kho con')

@section('content')

{{-- Warehouse Tabs --}}
@can('view-inventory')
<div class="flex items-center gap-1 mb-4 p-1 rounded-xl w-fit" style="background:var(--surface-card);border:1px solid var(--surface-border)">
    <a href="{{ route('inventory.index') }}"
       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 hover:opacity-80"
       style="color:var(--text-secondary)">
        <i class="bi bi-building text-sm"></i>
        <span>Kho Tổng (40)</span>
    </a>
    @foreach($destinations as $dest)
    <a href="{{ route('reports.destination-inventory', array_merge(request()->except(['destination_id','page']), ['destination_id' => $dest->id])) }}"
       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 hover:opacity-80 {{ request('destination_id') == $dest->id ? 'text-white shadow-sm' : '' }}"
       style="{{ request('destination_id') == $dest->id ? 'background:#4f46e5' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-box-seam text-sm"></i>
        <span>{{ $dest->name }}</span>
    </a>
    @endforeach
    <a href="{{ route('reports.destination-inventory', request()->except(['destination_id','page'])) }}"
       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 hover:opacity-80 {{ !request('destination_id') ? 'text-white shadow-sm' : '' }}"
       style="{{ !request('destination_id') ? 'background:#4f46e5' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-grid text-sm"></i>
        <span>Tất cả kho</span>
    </a>
</div>
@endcan

{{-- Summary cards --}}
@if($grouped->isNotEmpty())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min($destinations->count(), 4) }} gap-4 mb-4">
    @foreach($destinations as $dest)
    @php
    $destRows   = $grouped->get($dest->id, collect());
    $totalQty   = $destRows->sum('qty');
    $totalValue = $destRows->sum('value');
    $skuCount   = $destRows->count();
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 flex items-start gap-3"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(59,130,246,0.10)">
            <i class="ph ph-warehouse text-xl" style="color:#3b82f6"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-semibold truncate" style="color:var(--text-primary)">{{ $dest->name }}</p>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ number_format($skuCount) }} mặt hàng · {{ number_format($totalQty, 0, ',', '.') }} đơn vị</p>
            <p class="text-base font-bold mt-1" style="color:#3b82f6">{{ number_format($totalValue, 0, ',', '.') }}đ</p>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end" style="border-bottom:1px solid var(--surface-border)">
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn tại ngày</label>
            <x-date-picker name="as_of" :value="$asOf" max-date="today" class="w-40" placeholder="Chọn ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Kho nhận</label>
            <select name="destination_id" class="form-input h-9 text-sm w-40">
                <option value="">Tất cả kho</option>
                @foreach($destinations as $dest)
                <option value="{{ $dest->id }}" {{ request('destination_id') == $dest->id ? 'selected' : '' }}>{{ $dest->name }}</option>
                @endforeach
            </select>
        </div>
        @php
        $productItems = array_merge(
            [['v' => '', 'l' => 'Tất cả sản phẩm']],
            $products->map(fn($p) => ['v' => $p->id, 'l' => $p->name, 's' => $p->sku ?? ''])->toArray()
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
            <button type="button" class="sp-trigger w-48" @click="openPalette()">
                <span x-text="currentLabel || 'Tất cả sản phẩm'"></span>
                <i class="bi bi-search"></i>
            </button>
            @include('partials.select-palette', ['placeholder' => 'Tìm theo tên, SKU...', 'countLabel' => 'sản phẩm'])
        </div>
        <div class="flex gap-2 self-end">
            <button type="submit" class="h-9 px-4 text-sm font-medium text-white rounded-xl transition-colors"
                    style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                <i class="ph ph-magnifying-glass mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('reports.destination-inventory.export', request()->query()) }}"
               class="h-9 px-4 inline-flex items-center gap-1.5 text-sm font-medium text-white rounded-xl transition-colors"
               style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <i class="ph ph-file-xls text-base"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Info bar --}}
    <div class="px-4 py-2.5 flex items-center justify-between text-sm" style="background:rgba(59,130,246,0.04);border-bottom:1px solid var(--surface-border)">
        <span class="flex items-center gap-1.5" style="color:#2563eb">
            <i class="ph ph-calendar-check"></i>
            Tồn lũy kế tại ngày <strong>{{ \Carbon\Carbon::parse($asOf)->format('d/m/Y') }}</strong>
            @if($asOf >= $today)
            <span class="ml-1 text-xs px-1.5 py-0.5 rounded-md" style="background:rgba(59,130,246,0.12);color:#2563eb">Hôm nay</span>
            @endif
        </span>
        <span class="font-semibold" style="color:#2563eb">
            Tổng: {{ number_format($grouped->sum(fn($rows) => $rows->sum('value')), 0, ',', '.') }}đ
        </span>
    </div>

    {{-- Grouped tables --}}
    @if($grouped->isEmpty())
    <div class="px-4 py-16 text-center">
        <i class="ph ph-warehouse text-5xl block mb-3" style="color:var(--text-muted);opacity:.3"></i>
        <p class="text-base font-medium mb-1" style="color:var(--text-muted)">Không có dữ liệu tồn kho tại các kho con</p>
        <p class="text-sm" style="color:var(--text-muted)">Chưa có phiếu xuất kho được duyệt đến ngày đã chọn.</p>
    </div>
    @else
    @foreach($destinations as $dest)
    @php
    $destRows  = $grouped->get($dest->id, collect());
    $destQty   = $destRows->sum('qty');
    $destValue = $destRows->sum('value');
    @endphp
    @if($destRows->isNotEmpty())
    <div>
        <div class="px-4 py-2.5 flex items-center justify-between" style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
            <div class="flex items-center gap-2">
                <i class="ph ph-warehouse text-sm" style="color:var(--text-muted)"></i>
                <span class="font-semibold text-sm" style="color:var(--text-primary)">{{ $dest->name }}</span>
                <span class="text-xs" style="color:var(--text-muted)">— {{ number_format($destRows->count()) }} mặt hàng, {{ number_format($destQty, 0, ',', '.') }} đơn vị</span>
            </div>
            <span class="text-sm font-bold" style="color:#3b82f6">{{ number_format($destValue, 0, ',', '.') }}đ</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SKU</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Danh mục</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SL lũy kế</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá vốn TB</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá trị</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($destRows as $item)
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                        <td class="px-4 py-2.5 font-mono text-xs" style="color:var(--text-muted)">{{ $item->product?->sku ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-medium" style="color:var(--text-primary)">{{ $item->product?->name }}</td>
                        <td class="px-4 py-2.5 text-xs" style="color:var(--text-muted)">{{ $item->product?->category?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-xs" style="color:var(--text-muted)">{{ $item->product?->unit?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold tabular-nums" style="color:var(--text-primary)">{{ number_format($item->qty, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right tabular-nums text-xs" style="color:var(--text-muted)">{{ number_format($item->avg_cost, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-2.5 text-right font-medium tabular-nums" style="color:var(--text-primary)">{{ number_format($item->value, 0, ',', '.') }}đ</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--surface-bg);border-top:2px solid var(--surface-border)">
                        <td colspan="4" class="px-4 py-2.5 text-sm font-semibold" style="color:var(--text-secondary)">Tổng {{ $dest->name }}</td>
                        <td class="px-4 py-2.5 text-right text-sm font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($destQty, 0, ',', '.') }}</td>
                        <td></td>
                        <td class="px-4 py-2.5 text-right text-sm font-bold tabular-nums" style="color:#3b82f6">{{ number_format($destValue, 0, ',', '.') }}đ</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
    @endforeach
    @endif
</div>
@endsection
