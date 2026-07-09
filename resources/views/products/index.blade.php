@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page-title', 'Sản phẩm')
@section('breadcrumb', 'Danh mục / Sản phẩm')

@section('content')

<script>
const productUnitsMap = @js($units->pluck('name', 'id')->all());
</script>

<div x-data="{ openCreate: false, openEdit: false, editProduct: null }">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2.5">
            <span class="text-sm font-medium" style="color:var(--text-secondary)">Sản phẩm</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $products->total() }}</span>
        </div>
        <div class="flex gap-2 flex-wrap">
            @can('export-products')
            <a href="{{ route('products.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
               style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <i class="ph ph-file-xls text-base"></i> Xuất Excel
            </a>
            @endcan
            @can('create-products')
            <button x-data @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border transition-colors bg-emerald-600 dark:bg-emerald-700 text-white"
                    >
                <i class="ph ph-file-arrow-up text-base"></i> Import Excel
            </button>
            <button @click="openCreate = true"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                    style="background:#4f46e5"
                    onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                <i class="ph ph-plus text-base"></i> Thêm sản phẩm
            </button>
            @endcan
        </div>
    </div>

    {{-- ── Table Card ───────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

        {{-- Filter --}}
        <form method="GET" class="p-4 flex flex-wrap gap-3" style="border-bottom:1px solid var(--surface-border)">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm SKU, tên..."
                   class="form-input flex-1 min-w-[160px] h-9 text-sm">
            <select name="category_id" class="form-input flex-1 min-w-[140px] h-9 text-sm">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-input flex-1 min-w-[130px] h-9 text-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Hoạt động</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngừng</option>
            </select>
            <button type="submit"
                    class="h-9 px-4 text-sm font-medium rounded-xl transition-colors"
                    style="background:var(--surface-bg);border:1px solid var(--surface-border);color:var(--text-secondary)"
                    onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="ph ph-magnifying-glass mr-1"></i> Lọc
            </button>
            @if(request()->hasAny(['search','category_id','status']))
            <a href="{{ route('products.index') }}"
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
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ảnh</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SKU</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide min-w-[160px]" style="color:var(--text-muted)">Tên sản phẩm</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Danh mục</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Quy đổi</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn kho</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Trạng thái</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    @php $belowMin = $product->isBelowMinStock(); @endphp
                    <tr class="border-t transition-colors {{ $belowMin ? 'border-red-100 dark:border-red-900/30 bg-red-50/50 dark:bg-red-900/5 hover:bg-red-50 dark:hover:bg-red-900/10' : 'border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025]' }}">
                        <td class="px-5 py-3.5">
                            <div class="w-9 h-9 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0" style="background:var(--surface-bg);border:1px solid var(--surface-border)">
                                @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                <i class="ph ph-image text-sm" style="color:var(--text-muted)"></i>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-xs" style="color:var(--text-muted)">{{ $product->sku }}</td>
                        <td class="px-5 py-3.5 font-medium text-xs whitespace-normal break-words max-w-[240px] {{ $belowMin ? 'text-red-700 dark:text-red-400' : '' }}" style="{{ $belowMin ? '' : 'color:var(--text-primary)' }}">
                            <div class="flex items-center gap-1.5">
                                {{ $product->name }}
                                @if($belowMin)
                                <i class="ph ph-warning-circle text-xs text-red-500" title="Dưới ngưỡng tồn tối thiểu ({{ number_format($product->min_stock,0,',','.') }})"></i>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-xs font-medium" style="color:var(--text-secondary)">{{ $product->unit?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @if($product->unitConversions->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($product->unitConversions as $conv)
                                <span class="badge-blue inline-flex text-[10px] font-medium px-1.5 py-0.5 rounded-full">
                                    1 {{ $conv->unit?->name }} = {{ number_format($conv->factor, $conv->factor == floor($conv->factor) ? 0 : 3) }} {{ $product->unit?->name }}
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-xs
                            {{ $belowMin ? 'text-red-600 dark:text-red-400' : (($product->inventory?->quantity ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}"
                            style="{{ !$belowMin && ($product->inventory?->quantity ?? 0) <= 0 ? 'color:var(--text-muted)' : '' }}">
                            {{ number_format($product->inventory?->quantity ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($product->status === 'active')
                            <span class="badge-green inline-flex px-2 py-0.5 rounded-full text-xs font-medium">Hoạt động</span>
                            @else
                            <span class="badge-gray inline-flex px-2 py-0.5 rounded-full text-xs font-medium">Ngừng</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-products')
                                <button @click="editProduct = {{ $product->toJson() }}; openEdit = true"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                        onmouseover="this.style.background='rgba(99,102,241,0.08)';this.style.color='#4f46e5'"
                                        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                    <i class="ph ph-pencil-simple text-xs"></i>
                                </button>
                                @endcan
                                @can('delete-products')
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Xóa sản phẩm {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                            onmouseover="this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'"
                                            onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                        <i class="ph ph-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-16 text-center">
                            <i class="ph ph-package text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                            <p class="text-xs" style="color:var(--text-muted)">Chưa có sản phẩm nào</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    {{-- Modals --}}
    @include('products.partials.create-modal')
    @include('products.partials.edit-modal')

    {{-- Import Modal --}}
    <div x-data="productImportModal()"
         x-on:open-import-modal.window="open = true"
         x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="open = false"></div>
        <div class="modal-panel relative max-h-[92vh] overflow-y-auto p-6 transition-all duration-200" :class="result ? 'max-w-[min(48rem,98vw)]' : 'max-w-[min(28rem,98vw)]'"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(22,163,74,0.10)">
                        <i class="ph ph-file-arrow-up text-xs" style="color:#16a34a"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Import từ Excel</h3>
                </div>
                <button @click="open = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-xs"></i>
                </button>
            </div>
            <p class="text-xs mb-3 p-3 rounded-xl" style="color:var(--text-muted);background:var(--surface-bg)">
                File cần có cột: <code class="font-mono font-semibold" style="color:var(--text-primary)">ten_san_pham, sku, don_vi_tinh, danh_muc, gia_mac_dinh</code>.
                Sản phẩm trùng SKU sẽ được <strong>cập nhật</strong>, SKU mới sẽ <strong>tạo mới</strong>.
            </p>
            <a href="{{ route('products.import-template') }}"
               class="flex items-center gap-2 mb-4 px-3 py-2.5 rounded-xl text-sm font-medium border transition-colors"
               style="border-color:#16a34a33;color:#16a34a;background:rgba(22,163,74,0.06)"
               onmouseover="this.style.background='rgba(22,163,74,0.12)'" onmouseout="this.style.background='rgba(22,163,74,0.06)'">
                <i class="ph ph-file-xls text-xs"></i>
                Tải file mẫu Excel
            </a>

            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4" @submit="if (!checked) $event.preventDefault()">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Chọn file <span class="text-red-500 normal-case">*</span></label>
                    <input type="file" name="file" x-ref="fileInput" required accept=".xlsx,.xls,.csv" class="form-input" @change="onFileChange">
                </div>

                {{-- Kết quả kiểm tra --}}
                <template x-if="error">
                    <p class="text-xs px-3 py-2 rounded-xl" style="background:rgba(220,38,38,0.08);color:#dc2626" x-text="error"></p>
                </template>

                <template x-if="result">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-full font-semibold" style="background:rgba(22,163,74,0.10);color:#16a34a">
                                <i class="ph ph-check-circle mr-1"></i><span x-text="result.valid"></span> tạo mới
                            </span>
                            <span class="px-2.5 py-1 rounded-full font-semibold" style="background:rgba(37,99,235,0.10);color:#2563eb" x-show="result.update > 0">
                                <i class="ph ph-arrow-clockwise mr-1"></i><span x-text="result.update"></span> cập nhật
                            </span>
                            <span class="px-2.5 py-1 rounded-full font-semibold" style="background:rgba(217,119,6,0.10);color:#d97706" x-show="result.skipped > 0">
                                <i class="ph ph-warning-circle mr-1"></i><span x-text="result.skipped"></span> sẽ bị bỏ qua
                            </span>
                            <span class="text-xs" style="color:var(--text-muted)" x-show="result.truncated">
                                (chỉ hiển thị 300 dòng đầu tiên)
                            </span>
                        </div>

                        <div class="rounded-xl border overflow-hidden" style="border-color:var(--surface-border)">
                            <div class="max-h-64 overflow-y-auto overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead class="sticky top-0" style="background:var(--surface-bg)">
                                        <tr style="color:var(--text-muted)">
                                            <th class="text-left font-semibold px-3 py-2">Dòng</th>
                                            <th class="text-left font-semibold px-3 py-2">Tên sản phẩm</th>
                                            <th class="text-left font-semibold px-3 py-2">SKU</th>
                                            <th class="text-left font-semibold px-3 py-2">Danh mục</th>
                                            <th class="text-left font-semibold px-3 py-2">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="row in result.rows" :key="row.row">
                                            <tr class="border-t" style="border-color:var(--surface-border)">
                                                <td class="px-3 py-2" style="color:var(--text-muted)" x-text="row.row"></td>
                                                <td class="px-3 py-2" style="color:var(--text-primary)" x-text="row.name || '—'"></td>
                                                <td class="px-3 py-2" style="color:var(--text-secondary)" x-text="row.sku || '—'"></td>
                                                <td class="px-3 py-2" style="color:var(--text-secondary)" x-text="row.category || '—'"></td>
                                                <td class="px-3 py-2">
                                                    <span x-show="row.status === 'valid'" class="px-2 py-0.5 rounded-full font-medium" style="background:rgba(22,163,74,0.10);color:#16a34a">Tạo mới</span>
                                                    <span x-show="row.status === 'update'" class="px-2 py-0.5 rounded-full font-medium" style="background:rgba(37,99,235,0.10);color:#2563eb" x-text="row.reason || 'Cập nhật'"></span>
                                                    <span x-show="row.status === 'invalid'" class="px-2 py-0.5 rounded-full font-medium" style="background:rgba(220,38,38,0.10);color:#dc2626" x-text="row.reason"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                    <button type="button" @click="check" :disabled="checking"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl border transition-colors disabled:opacity-60"
                            style="border-color:var(--surface-border);color:var(--text-secondary)"
                            onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                        <i class="ph ph-magnifying-glass text-base" x-show="!checking"></i>
                        <i class="ph ph-circle-notch text-base animate-spin" x-show="checking"></i>
                        <span x-text="checking ? 'Đang kiểm tra…' : 'Kiểm tra dữ liệu'"></span>
                    </button>
                    <button type="submit" :disabled="!checked || (result?.valid ?? 0) + (result?.update ?? 0) === 0"
                            class="px-4 py-2 text-sm font-medium text-white rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background:#16a34a" onmouseover="if(!this.disabled)this.style.background='#15803d'" onmouseout="if(!this.disabled)this.style.background='#16a34a'">
                        <i class="ph ph-upload mr-1"></i> Import<template x-if="result"> (<span x-text="result.valid + result.update"></span>)</template>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function productImportModal() {
        return {
            open: false,
            checking: false,
            checked: false,
            error: null,
            result: null,
            onFileChange() {
                this.checked = false;
                this.result = null;
                this.error = null;
            },
            async check() {
                const file = this.$refs.fileInput.files[0];
                if (!file) {
                    this.error = 'Vui lòng chọn file trước khi kiểm tra.';
                    return;
                }
                this.checking = true;
                this.error = null;
                this.result = null;
                this.checked = false;

                const fd = new FormData();
                fd.append('file', file);
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

                try {
                    const resp = await fetch('{{ route('products.import-preview') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: fd,
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.error = data.message || 'Không đọc được file. Vui lòng kiểm tra định dạng.';
                        return;
                    }
                    this.result = data;
                    this.checked = true;
                } catch (e) {
                    this.error = 'Có lỗi xảy ra khi kiểm tra file.';
                } finally {
                    this.checking = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
