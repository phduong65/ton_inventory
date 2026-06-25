@extends('layouts.admin')

@section('title', $destination ? 'Kiểm kê ' . $destination->name : 'Tạo phiếu kiểm kê')
@section('page-title', $destination ? 'Kiểm kê ' . $destination->name : 'Tạo phiếu kiểm kê')
@section('breadcrumb', 'Kiểm kê / Tạo phiếu')

@section('content')

@if(session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div x-data="stocktakeForm()">
<form action="{{ route('stocktakes.store') }}" method="POST">
    @csrf

    @if($destination)
    <input type="hidden" name="destination_id" value="{{ $destination->id }}">
    @endif

    {{-- ── Action bar ──────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 mb-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">

            {{-- Left: back + badge + ghi chú --}}
            <div class="flex items-center gap-2 flex-wrap min-w-0">
                <a href="{{ route('stocktakes.index') }}" class="btn-icon" title="Quay lại">
                    <i class="bi bi-arrow-left" style="font-size:13px"></i>
                </a>
                @if($destination)
                <span class="kk-type-badge" style="background:rgba(37,99,235,0.10); color:#2563eb; white-space:nowrap">
                    <i class="ph ph-warehouse" style="font-size:11px"></i> {{ $destination->name }}
                </span>
                @else
                <span class="kk-type-badge" style="white-space:nowrap">
                    <i class="bi bi-clipboard-check" style="font-size:11px"></i> Kho Tổng
                </span>
                @endif
                <input type="text" name="note" value="{{ old('note') }}" placeholder="Ghi chú..."
                       style="font-size:13px; padding:5px 10px; border-radius:8px; border:1px solid var(--surface-border);
                              background:var(--surface-bg); color:var(--text-primary); width:220px; outline:none"
                       onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='var(--surface-border)'">
            </div>

            {{-- Right: action buttons --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('stocktakes.index') }}" class="btn-ghost">Hủy</a>
                <button type="submit" name="save" value="1" class="btn-outline">
                    <i class="bi bi-floppy" style="font-size:12px"></i> Lưu nháp
                </button>
                <button type="submit" name="submit" value="1" class="{{ $destination ? 'btn-primary-blue' : 'btn-primary' }}">
                    <i class="bi bi-send" style="font-size:11px"></i> Gửi chờ duyệt
                </button>
            </div>
        </div>

        {{-- Category scope selector — chỉ hiện ở chế độ Kho Tổng --}}
        @if(!$destination)
        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide flex-shrink-0">Phạm vi:</span>
            <label class="kk-scope-btn">
                <input type="radio" name="category_id" value="" x-model="selectedCategoryId" style="display:none">
                <span :class="selectedCategoryId === '' ? 'kk-scope-active' : ''">
                    <i class="bi bi-grid-3x3-gap" style="font-size:11px"></i> Tất cả
                </span>
            </label>
            @foreach($rootCategories as $cat)
            <label class="kk-scope-btn">
                <input type="radio" name="category_id" value="{{ $cat->id }}" x-model="selectedCategoryId" style="display:none">
                <span :class="selectedCategoryId == '{{ $cat->id }}' ? 'kk-scope-active' : ''">
                    {{ $cat->name }}
                </span>
            </label>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Product table ────────────────────────────────────────────── --}}
    <div class="create-card">

        {{-- Toolbar: search full-width --}}
        <div class="create-card-header" style="gap:10px; padding:10px 16px">
            <div class="create-accent-bar" style="background:{{ $destination ? '#2563eb' : '#7c3aed' }}; flex-shrink:0"></div>
            {{-- Search chiếm toàn bộ chiều rộng còn lại --}}
            <div style="flex:1; position:relative">
                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--text-muted); pointer-events:none"></i>
                <input type="text" x-model="search" placeholder="Tìm sản phẩm, SKU..."
                       style="width:100%; font-size:13px; padding:6px 10px 6px 30px; border-radius:8px;
                              border:1px solid var(--surface-border); background:var(--surface-bg);
                              color:var(--text-primary); outline:none; box-sizing:border-box"
                       onfocus="this.style.borderColor='{{ $destination ? '#2563eb' : '#7c3aed' }}'"
                       onblur="this.style.borderColor='var(--surface-border)'">
            </div>
        </div>

        {{-- Hint row --}}
        <p style="padding:7px 16px; font-size:12px; color:var(--text-muted); border-bottom:1px solid var(--surface-border)">
            @if($destination)
                Nhập SL thực tế tại <strong>{{ $destination->name }}</strong>. Để trống = bỏ qua.
                <span style="color:#2563eb">Tồn HT = tổng đã xuất đến kho này.</span>
            @else
                Nhập số lượng thực tế đếm được. Để trống = bỏ qua sản phẩm đó.
            @endif
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead style="background:var(--surface-bg); border-bottom:1px solid var(--surface-border)">
                    <tr>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:44px">#</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em">Sản phẩm</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:80px">ĐVT</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:120px">Danh mục</th>
                        <th style="padding:10px 12px; text-align:right; font-size:11px; font-weight:600; color:{{ $destination ? '#2563eb' : 'var(--text-muted)' }}; text-transform:uppercase; letter-spacing:.05em; width:140px">
                            {{ $destination ? 'Đã nhận (HT)' : 'Tồn hệ thống' }}
                        </th>
                        <th style="padding:10px 12px; text-align:right; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:160px">SL thực tế</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $i => $product)
                    @php
                        $sysQty = $destination
                            ? ($product->destination_qty ?? 0)
                            : ($product->inventory?->quantity ?? 0);
                    @endphp
                    <tr class="kk-row"
                        x-show="isVisible({{ $product->id }}, {{ Js::from($product->name) }})"
                        style="border-top:1px solid var(--surface-border)">
                        <td style="padding:10px 16px">
                            <span class="kk-row-num">{{ $i + 1 }}</span>
                        </td>
                        <td style="padding:10px 12px; font-weight:500; color:var(--text-primary)">
                            {{ $product->name }}
                            @if($product->sku)
                            <span style="font-size:11px; color:var(--text-muted); font-weight:400; margin-left:4px">{{ $product->sku }}</span>
                            @endif
                            <input type="hidden" name="details[{{ $i }}][product_id]" value="{{ $product->id }}"
                                   :disabled="!isVisible({{ $product->id }}, {{ Js::from($product->name) }})">
                            <input type="hidden" name="details[{{ $i }}][system_qty]" value="{{ $sysQty }}"
                                   :disabled="!isVisible({{ $product->id }}, {{ Js::from($product->name) }})">
                        </td>
                        <td style="padding:10px 12px; color:var(--text-secondary); font-size:13px">{{ $product->unit?->name ?? '—' }}</td>
                        <td style="padding:10px 12px; font-size:12px; color:var(--text-muted)">{{ $product->category?->name ?? '—' }}</td>
                        <td style="padding:10px 12px; text-align:right; color:{{ $destination ? '#2563eb' : 'var(--text-secondary)' }}; font-size:13.5px; font-variant-numeric:tabular-nums; font-weight:{{ $destination ? '600' : '400' }}">
                            {{ number_format($sysQty, 0, ',', '.') }}
                        </td>
                        <td style="padding:8px 12px; text-align:right">
                            <input type="number" name="details[{{ $i }}][actual_qty]"
                                   min="0" step="0.001" placeholder="—"
                                   class="kk-qty-input {{ $destination ? 'kk-qty-blue' : '' }}"
                                   :disabled="!isVisible({{ $product->id }}, {{ Js::from($product->name) }})">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($products->isEmpty())
        <div style="padding:48px 20px; text-align:center; color:var(--text-muted)">
            <i class="ph ph-package" style="font-size:40px; display:block; margin-bottom:10px; opacity:.4"></i>
            <p style="font-size:14px">Chưa có sản phẩm nào được xuất đến kho này.</p>
            <p style="font-size:12px; margin-top:4px">Tạo phiếu xuất kho trước khi kiểm kê.</p>
        </div>
        @endif
    </div>

</form>
</div>

<style>
.btn-icon {
    width:32px; height:32px; border-radius:8px;
    display:inline-flex; align-items:center; justify-content:center;
    background:var(--surface-card); border:1px solid var(--surface-border);
    color:var(--text-secondary); text-decoration:none; cursor:pointer;
    transition:background .15s, color .15s; flex-shrink:0;
}
.btn-icon:hover { background:var(--surface-bg); color:var(--text-primary); }
.kk-type-badge {
    font-size:11px; font-weight:700; padding:3px 10px; border-radius:6px;
    background:rgba(124,58,237,0.10); color:#7c3aed;
    display:inline-flex; align-items:center; gap:4px;
}
.btn-ghost {
    font-size:13.5px; padding:7px 14px; border-radius:8px;
    background:transparent; border:1px solid var(--surface-border);
    color:var(--text-secondary); text-decoration:none; cursor:pointer;
    white-space:nowrap; transition:background .15s, color .15s;
}
.btn-ghost:hover { background:var(--surface-bg); color:var(--text-primary); }
.btn-outline {
    font-size:13.5px; padding:7px 14px; border-radius:8px;
    background:var(--surface-card); border:1px solid var(--surface-border);
    color:var(--text-primary); cursor:pointer; white-space:nowrap;
    display:inline-flex; align-items:center; gap:6px; transition:background .15s;
}
.btn-outline:hover { background:var(--surface-bg); }
.btn-primary {
    font-size:13.5px; font-weight:600; padding:7px 16px; border-radius:8px;
    color:#fff; background:#7c3aed; border:none; cursor:pointer; white-space:nowrap;
    display:inline-flex; align-items:center; gap:6px; transition:background .15s;
}
.btn-primary:hover { background:#6d28d9; }
.btn-primary-blue {
    font-size:13.5px; font-weight:600; padding:7px 16px; border-radius:8px;
    color:#fff; background:#2563eb; border:none; cursor:pointer; white-space:nowrap;
    display:inline-flex; align-items:center; gap:6px; transition:background .15s;
}
.btn-primary-blue:hover { background:#1d4ed8; }
.create-card {
    background:var(--surface-card); border:1px solid var(--surface-border);
    border-radius:12px; overflow:hidden;
}
.create-card-header {
    display:flex; align-items:center; gap:10px; padding:12px 20px;
    border-bottom:1px solid var(--surface-border);
}
.create-accent-bar { width:3px; height:16px; border-radius:2px; flex-shrink:0; }
.kk-scope-btn { cursor:pointer; }
.kk-scope-btn span {
    display:inline-flex; align-items:center; gap:5px;
    font-size:12px; font-weight:500; padding:4px 12px; border-radius:8px;
    border:1.5px solid var(--surface-border); color:var(--text-secondary);
    transition:all .15s; cursor:pointer; user-select:none;
}
.kk-scope-btn span:hover { border-color:#7c3aed; color:#7c3aed; background:rgba(124,58,237,0.05); }
.kk-scope-active {
    border-color:#7c3aed !important; color:#7c3aed !important;
    background:rgba(124,58,237,0.08) !important;
}
.kk-row { transition:background .1s; }
.kk-row:hover { background:var(--surface-bg); }
.kk-row-num {
    display:inline-flex; width:22px; height:22px; border-radius:50%;
    align-items:center; justify-content:center;
    background:var(--surface-bg); border:1px solid var(--surface-border);
    font-size:11px; color:var(--text-muted); font-weight:600;
}
.kk-qty-input {
    width:120px; padding:6px 10px; text-align:right;
    border:1.5px solid var(--surface-border); border-radius:8px;
    background:var(--surface-bg); color:var(--text-primary);
    font-size:13.5px; outline:none; transition:border-color .15s;
}
.kk-qty-input:focus { border-color:#7c3aed; background:var(--surface-card); }
.kk-qty-input:not(:placeholder-shown) {
    border-color:#7c3aed; background:rgba(124,58,237,0.04);
    color:#7c3aed; font-weight:600;
}
.kk-qty-blue:focus { border-color:#2563eb !important; }
.kk-qty-blue:not(:placeholder-shown) {
    border-color:#2563eb !important; background:rgba(37,99,235,0.04) !important;
    color:#2563eb !important;
}
</style>

@php
$productCategoryMap = $products->mapWithKeys(fn($p) => [
    $p->id => [
        'rootId' => $p->category?->getRootId(),
        'name'   => $p->name,
        'sku'    => $p->sku ?? '',
    ]
]);
@endphp
<script>
const productCategoryMap = {!! json_encode($productCategoryMap) !!};

function stocktakeForm() {
    return {
        selectedCategoryId: '{{ old('category_id', '') }}',
        search: '',
        isVisible(productId, productName) {
            const p = productCategoryMap[productId];
            const matchesScope = this.selectedCategoryId === '' ||
                p?.rootId == this.selectedCategoryId;
            const q = this.search.toLowerCase();
            const matchesSearch = q === '' ||
                productName.toLowerCase().includes(q) ||
                (p?.sku ?? '').toLowerCase().includes(q);
            return matchesScope && matchesSearch;
        },
    };
}
</script>
@endsection
