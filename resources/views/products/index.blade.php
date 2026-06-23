@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page-title', 'Sản phẩm')
@section('breadcrumb', 'Danh mục / Sản phẩm')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editProduct: null }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $products->total() }} sản phẩm</p>
        <div class="flex gap-2">
            @can('create-products')
            <button @click="openCreate = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="bi bi-plus-lg"></i> Thêm sản phẩm
            </button>
            @endcan
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Filter --}}
        <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm SKU, tên..."
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-56">
            <select name="category_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngừng</option>
            </select>
            <button type="submit" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                <i class="bi bi-search mr-1"></i> Lọc
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Tên sản phẩm</th>
                        <th class="px-4 py-3">Danh mục</th>
                        <th class="px-4 py-3">ĐVT</th>
                        <th class="px-4 py-3 text-right">Tồn kho</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $product->sku }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->unit }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ ($product->inventory?->quantity ?? 0) > 0 ? 'text-green-600' : 'text-gray-400' }}">
                            {{ number_format($product->inventory?->quantity ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($product->status === 'active')
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Hoạt động</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Ngừng</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-products')
                                <button @click="editProduct = {{ $product->toJson() }}; openEdit = true"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded">
                                    <i class="bi bi-pencil text-xs"></i>
                                </button>
                                @endcan
                                @can('delete-products')
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Xóa sản phẩm {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="ph-package text-4xl block mb-2"></i>
                            Chưa có sản phẩm nào
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    {{-- Create Modal --}}
    @include('products.partials.create-modal')

    {{-- Edit Modal --}}
    @include('products.partials.edit-modal')
</div>
@endsection
