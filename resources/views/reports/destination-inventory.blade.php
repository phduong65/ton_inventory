@extends('layouts.admin')

@section('title', 'Tồn kho Kho con')
@section('page-title', 'Tồn kho Kho con')
@section('breadcrumb', 'Báo cáo / Tồn kho Kho con')

@section('content')

{{-- Warehouse Tabs --}}
@can('view-inventory')
<div class="flex items-center gap-1 mb-4 p-1 rounded-xl w-fit" style="background:var(--surface-card); border:1px solid var(--surface-border)">
    <a href="{{ route('inventory.index') }}"
       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 hover:opacity-80"
       style="color:var(--text-secondary)">
        <i class="bi bi-building text-sm"></i>
        <span>Kho Tổng (40)</span>
    </a>
    @foreach($destinations as $dest)
    <a href="{{ route('reports.destination-inventory', array_merge(request()->except(['destination_id','page']), ['destination_id' => $dest->id])) }}"
       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 hover:opacity-80
              {{ request('destination_id') == $dest->id ? 'text-white shadow-sm' : '' }}"
       style="{{ request('destination_id') == $dest->id ? 'background:#4f46e5' : 'color:var(--text-secondary)' }}">
        <i class="bi bi-box-seam text-sm"></i>
        <span>{{ $dest->name }}</span>
    </a>
    @endforeach
    {{-- Tab tất cả --}}
    <a href="{{ route('reports.destination-inventory', request()->except(['destination_id','page'])) }}"
       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 hover:opacity-80
              {{ !request('destination_id') ? 'text-white shadow-sm' : '' }}"
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
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-start gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 bg-blue-100 dark:bg-blue-900/30">
            <i class="ph ph-warehouse text-xl text-blue-600 dark:text-blue-400"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $dest->name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ number_format($skuCount) }} mặt hàng · {{ number_format($totalQty, 0, ',', '.') }} đơn vị</p>
            <p class="text-base font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($totalValue, 0, ',', '.') }}đ</p>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- Filter --}}
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Tồn tại ngày</label>
            <x-date-picker name="as_of" :value="$asOf" max-date="today" class="w-40" placeholder="Chọn ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Kho nhận</label>
            <select name="destination_id"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-40">
                <option value="">Tất cả kho</option>
                @foreach($destinations as $dest)
                <option value="{{ $dest->id }}" {{ request('destination_id') == $dest->id ? 'selected' : '' }}>
                    {{ $dest->name }}
                </option>
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
            <label class="text-xs text-gray-500 dark:text-gray-400">Sản phẩm</label>
            <input type="hidden" name="product_id" :value="currentValue">
            <button type="button" class="sp-trigger w-48" @click="openPalette()">
                <span x-text="currentLabel || 'Tất cả sản phẩm'"></span>
                <i class="bi bi-search"></i>
            </button>
            @include('partials.select-palette', ['placeholder' => 'Tìm theo tên, SKU...', 'countLabel' => 'sản phẩm'])
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                <i class="bi bi-search mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('reports.destination-inventory.export', request()->query()) }}"
               class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center gap-1">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Info bar --}}
    <div class="px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm">
        <span class="text-blue-700 dark:text-blue-400 flex items-center gap-1.5">
            <i class="ph ph-calendar-check"></i>
            Tồn lũy kế tại ngày
            <strong>{{ \Carbon\Carbon::parse($asOf)->format('d/m/Y') }}</strong>
            @if($asOf >= $today)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300">Hôm nay</span>
            @endif
        </span>
        <span class="font-semibold text-blue-700 dark:text-blue-400">
            Tổng: {{ number_format($grouped->sum(fn ($rows) => $rows->sum('value')), 0, ',', '.') }}đ
        </span>
    </div>

    {{-- Grouped tables --}}
    @if($grouped->isEmpty())
    <div class="px-4 py-16 text-center text-gray-400">
        <i class="ph ph-warehouse text-5xl block mb-3"></i>
        <p class="text-base font-medium text-gray-500 dark:text-gray-400">Không có dữ liệu tồn kho tại các kho con</p>
        <p class="text-sm mt-1">Chưa có phiếu xuất kho được duyệt đến ngày đã chọn.</p>
    </div>
    @else
    @foreach($destinations as $dest)
    @php
        $destRows   = $grouped->get($dest->id, collect());
        $destQty    = $destRows->sum('qty');
        $destValue  = $destRows->sum('value');
    @endphp
    @if($destRows->isNotEmpty())
    <div>
        {{-- Destination header --}}
        <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/60 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="ph ph-warehouse text-base text-gray-500 dark:text-gray-400"></i>
                <span class="font-semibold text-gray-900 dark:text-white text-sm">{{ $dest->name }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    — {{ number_format($destRows->count()) }} mặt hàng, {{ number_format($destQty, 0, ',', '.') }} đơn vị
                </span>
            </div>
            <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                {{ number_format($destValue, 0, ',', '.') }}đ
            </span>
        </div>

        {{-- Products table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/50 dark:bg-gray-700/30 text-xs text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-2.5">SKU</th>
                        <th class="px-4 py-2.5">Sản phẩm</th>
                        <th class="px-4 py-2.5">Danh mục</th>
                        <th class="px-4 py-2.5">ĐVT</th>
                        <th class="px-4 py-2.5 text-right">SL lũy kế</th>
                        <th class="px-4 py-2.5 text-right">Giá vốn TB</th>
                        <th class="px-4 py-2.5 text-right">Giá trị</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($destRows as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $item->product?->sku ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $item->product?->name }}</td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $item->product?->category?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $item->product?->unit?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white">
                            {{ number_format($item->qty, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-400">
                            {{ number_format($item->avg_cost, 0, ',', '.') }}đ
                        </td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white">
                            {{ number_format($item->value, 0, ',', '.') }}đ
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-700/40 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <tr>
                        <td colspan="4" class="px-4 py-2.5">Tổng {{ $dest->name }}</td>
                        <td class="px-4 py-2.5 text-right">{{ number_format($destQty, 0, ',', '.') }}</td>
                        <td></td>
                        <td class="px-4 py-2.5 text-right text-blue-600 dark:text-blue-400">{{ number_format($destValue, 0, ',', '.') }}đ</td>
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
