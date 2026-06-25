@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page-title', 'Sản phẩm')
@section('breadcrumb', 'Danh mục / Sản phẩm')

@section('content')

{{-- Units map cho JS trong modals --}}
<script>
const productUnitsMap = @js($units->pluck('name', 'id')->all());
</script>

<div x-data="{ openCreate: false, openEdit: false, editProduct: null }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $products->total() }} sản phẩm</p>
        <div class="flex gap-2">
            @can('create-products')
            <button x-data @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <i class="bi bi-file-earmark-arrow-up"></i> Import Excel
            </button>
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
                   class="flex-1 min-w-[160px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <select name="category_id" class="flex-1 min-w-[140px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="flex-1 min-w-[130px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngừng</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-gray-700 dark:text-gray-300 font-medium">
                <i class="bi bi-search mr-1"></i> Lọc
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3 whitespace-normal min-w-[160px]">Tên sản phẩm</th>
                        <th class="px-4 py-3">Danh mục</th>
                        <th class="px-4 py-3">ĐVT</th>
                        <th class="px-4 py-3">Quy đổi</th>
                        <th class="px-4 py-3 text-right">Tồn kho</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($products as $product)
                    @php $belowMin = $product->isBelowMinStock(); @endphp
                    <tr class="{{ $belowMin ? 'bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $product->sku }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $product->category?->name ?? '—' }}</td>

                        {{-- ĐVT cơ sở --}}
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            {{ $product->unit?->name ?? '—' }}
                        </td>

                        {{-- Quy đổi --}}
                        <td class="px-4 py-3">
                            @if($product->unitConversions->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($product->unitConversions as $conv)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium
                                             bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                                    1 {{ $conv->unit?->name }} = {{ number_format($conv->factor, $conv->factor == floor($conv->factor) ? 0 : 3) }} {{ $product->unit?->name }}
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right font-medium tabular-nums
                            {{ $belowMin ? 'text-red-600 dark:text-red-400' : (($product->inventory?->quantity ?? 0) > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400') }}">
                            {{ number_format($product->inventory?->quantity ?? 0, 0, ',', '.') }}
                            @if($belowMin)
                            <i class="bi bi-exclamation-triangle-fill text-xs ml-0.5" title="Dưới ngưỡng tồn tối thiểu ({{ number_format($product->min_stock, 0, ',', '.') }})"></i>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($product->status === 'active')
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400">Hoạt động</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Ngừng</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-products')
                                {{-- Dùng $product->toJson() vì relations đã được eager load trong controller --}}
                                <button @click="editProduct = {{ $product->toJson() }}; openEdit = true"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded"
                                        title="Sửa">
                                    <i class="bi bi-pencil text-xs"></i>
                                </button>
                                @endcan
                                @can('delete-products')
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Xóa sản phẩm {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                            title="Xóa">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <i class="ph ph-package text-4xl block mb-2"></i>
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

    {{-- Import Modal --}}
    <div x-data="{ open: false }"
         x-on:open-import-modal.window="open = true"
         x-show="open"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         x-cloak>
        <div @click.outside="open = false"
             class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Import sản phẩm từ Excel</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                File Excel cần có các cột:
                <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">ten_san_pham</code>,
                <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">sku</code>,
                <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">don_vi_tinh</code>,
                <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">danh_muc</code>,
                <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">gia_mac_dinh</code>
            </p>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Chọn file <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        <i class="bi bi-upload mr-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
