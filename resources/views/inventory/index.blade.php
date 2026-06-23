@extends('layouts.admin')

@section('title', 'Tồn kho')
@section('page-title', 'Tồn kho hiện tại')
@section('breadcrumb', 'Kho / Tồn kho')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg px-4 py-2">
        <span class="text-sm text-green-700 dark:text-green-400 font-medium">
            Tổng giá trị tồn kho: <strong>{{ number_format($totalValue, 0, ',', '.') }}đ</strong>
        </span>
    </div>
    @can('export-inventory')
    <a href="{{ route('inventory.export', request()->query()) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <i class="bi bi-file-earmark-excel"></i> Xuất Excel
    </a>
    @endcan
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm SKU, tên..."
               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-56">
        <select name="category_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <input type="checkbox" name="has_stock" value="1" {{ request('has_stock') ? 'checked' : '' }}> Chỉ có tồn
        </label>
        <button type="submit" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg">
            <i class="bi bi-search mr-1"></i> Lọc
        </button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Tên sản phẩm</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">SL tồn</th>
                    <th class="px-4 py-3 text-right">Giá vốn</th>
                    <th class="px-4 py-3 text-right">Giá trị tồn</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->product?->sku }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item->product?->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $item->product?->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $item->product?->unit }}</td>
                    <td class="px-4 py-3 text-right font-medium {{ $item->quantity > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                        {{ number_format($item->quantity, 0, ',', '.') }}
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
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph-archive text-4xl block mb-2"></i>
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
@endsection
