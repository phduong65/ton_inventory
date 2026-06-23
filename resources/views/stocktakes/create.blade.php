@extends('layouts.admin')

@section('title', 'Tạo phiếu kiểm kê')
@section('page-title', 'Tạo phiếu kiểm kê')
@section('breadcrumb', 'Kiểm kê / Tạo phiếu')

@section('content')
<div x-data="stocktakeForm()" x-init="init()">
<form action="{{ route('stocktakes.store') }}" method="POST">
    @csrf

    {{-- Action bar --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px">
        <div style="display:flex; align-items:center; gap:10px">
            <a href="{{ route('stocktakes.index') }}" class="btn-icon" title="Quay lại">
                <i class="bi bi-arrow-left" style="font-size:13px"></i>
            </a>
            <span class="kk-type-badge">
                <i class="bi bi-clipboard-check" style="font-size:11px"></i> KIỂM KÊ
            </span>
            <span style="font-size:14px; font-weight:500; color:var(--text-secondary)">/ Phiếu mới</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px">
            <a href="{{ route('stocktakes.index') }}" class="btn-ghost">Hủy</a>
            <button type="submit" name="save" class="btn-outline">
                <i class="bi bi-floppy" style="font-size:12px"></i> Lưu nháp
            </button>
            <button type="submit" name="submit" class="btn-primary">
                <i class="bi bi-send" style="font-size:11px"></i> Submit chờ duyệt
            </button>
        </div>
    </div>

    {{-- Info panel: scope + ghi chú --}}
    <div class="create-card" style="margin-bottom:16px">
        <div class="create-card-header">
            <div class="create-accent-bar" style="background:#7c3aed"></div>
            <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">Phạm vi kiểm kê</span>
        </div>
        <div style="padding:16px; display:flex; gap:16px; align-items:flex-start">
            <div style="flex:2; min-width:0">
                <label class="create-label">Bộ phận / Danh mục</label>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:6px">
                    <label class="kk-scope-btn">
                        <input type="radio" name="category_id" value="" x-model="selectedCategoryId" @change="filterProducts()" style="display:none">
                        <span :class="selectedCategoryId === '' ? 'kk-scope-active' : ''">
                            <i class="bi bi-grid-3x3-gap" style="font-size:11px"></i> Tổng (tất cả)
                        </span>
                    </label>
                    @foreach($rootCategories as $cat)
                    <label class="kk-scope-btn">
                        <input type="radio" name="category_id" value="{{ $cat->id }}" x-model="selectedCategoryId" @change="filterProducts()" style="display:none">
                        <span :class="selectedCategoryId === '{{ $cat->id }}' ? 'kk-scope-active' : ''">
                            {{ $cat->name }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div style="flex:1; min-width:0">
                <label class="create-label">Ghi chú</label>
                <input type="text" name="note" placeholder="Ghi chú kiểm kê..." class="create-sidebar-input">
            </div>
        </div>
    </div>

    {{-- Product table --}}
    <div class="create-card">
        <div class="create-card-header" style="justify-content:space-between">
            <div style="display:flex; align-items:center; gap:10px">
                <div class="create-accent-bar" style="background:#7c3aed"></div>
                <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">
                    Chi tiết kiểm kê
                </span>
                <span x-text="visibleCount" style="font-size:12px; color:var(--text-muted); font-weight:500"></span>
                <span style="font-size:12px; color:var(--text-muted)">sản phẩm</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px">
                <input type="text" x-model="search" placeholder="Tìm sản phẩm..."
                       style="font-size:13px; padding:5px 10px; border-radius:7px; border:1px solid var(--surface-border);
                              background:var(--surface-bg); color:var(--text-primary); width:200px; outline:none">
            </div>
        </div>
        <p style="padding:8px 20px; font-size:12px; color:var(--text-muted); border-bottom:1px solid var(--surface-border)">
            Nhập số lượng thực tế đếm được. Để trống = bỏ qua sản phẩm đó.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead style="background:var(--surface-bg); border-bottom:1px solid var(--surface-border)">
                    <tr>
                        <th style="padding:10px 20px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:44px">#</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em">Sản phẩm</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:80px">ĐVT</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:120px">Danh mục</th>
                        <th style="padding:10px 12px; text-align:right; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:120px">Tồn hệ thống</th>
                        <th style="padding:10px 12px; text-align:right; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:160px">Số lượng thực tế</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $i => $product)
                    <tr class="kk-row"
                        data-product-id="{{ $product->id }}"
                        data-category-ids="{{ json_encode($product->category ? $product->category->allDescendantIds() : []) }}"
                        data-root-id="{{ $product->category?->getRootId() }}"
                        x-show="isVisible({{ $product->id }}, '{{ addslashes($product->name) }}')"
                        style="border-top:1px solid var(--surface-border)">
                        <td style="padding:10px 20px">
                            <span class="kk-row-num">{{ $i + 1 }}</span>
                        </td>
                        <td style="padding:10px 12px; font-weight:500; color:var(--text-primary)">
                            {{ $product->name }}
                            @if($product->sku)
                            <span style="font-size:11px; color:var(--text-muted); font-weight:400; margin-left:4px">{{ $product->sku }}</span>
                            @endif
                            <input type="hidden" name="details[{{ $i }}][product_id]" value="{{ $product->id }}">
                            <input type="hidden" name="details[{{ $i }}][system_qty]" value="{{ $product->inventory?->quantity ?? 0 }}">
                        </td>
                        <td style="padding:10px 12px; color:var(--text-secondary); font-size:13px">{{ $product->unit }}</td>
                        <td style="padding:10px 12px; font-size:12px; color:var(--text-muted)">
                            {{ $product->category?->name ?? '—' }}
                        </td>
                        <td style="padding:10px 12px; text-align:right; color:var(--text-secondary); font-size:13.5px; font-variant-numeric:tabular-nums">
                            {{ number_format($product->inventory?->quantity ?? 0, 0, ',', '.') }}
                        </td>
                        <td style="padding:8px 12px; text-align:right">
                            <input type="number" name="details[{{ $i }}][actual_qty]"
                                   min="0" step="0.001" placeholder="—"
                                   class="kk-qty-input">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</form>
</div>

<style>
.btn-icon {
    width:32px; height:32px; border-radius:8px;
    display:inline-flex; align-items:center; justify-content:center;
    background:var(--surface-card); border:1px solid var(--surface-border);
    color:var(--text-secondary); text-decoration:none; cursor:pointer;
    transition:background .15s, color .15s;
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
.create-card {
    background:var(--surface-card); border:1px solid var(--surface-border);
    border-radius:12px; overflow:hidden;
}
.create-card-header {
    display:flex; align-items:center; gap:10px; padding:12px 20px;
    border-bottom:1px solid var(--surface-border);
}
.create-accent-bar { width:3px; height:16px; border-radius:2px; flex-shrink:0; }
.create-label {
    display:block; font-size:12px; font-weight:600; color:var(--text-muted);
    text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px;
}
.create-sidebar-input {
    width:100%; background:transparent; border:none;
    border-bottom:1.5px solid var(--surface-border);
    color:var(--text-primary); font-size:13.5px;
    padding:6px 0; outline:none; transition:border-color .15s;
}
.create-sidebar-input:focus { border-bottom-color:#7c3aed; }

/* Scope selector */
.kk-scope-btn { cursor:pointer; }
.kk-scope-btn span {
    display:inline-flex; align-items:center; gap:5px;
    font-size:13px; font-weight:500; padding:6px 14px; border-radius:8px;
    border:1.5px solid var(--surface-border); color:var(--text-secondary);
    transition:all .15s; cursor:pointer; user-select:none;
}
.kk-scope-btn span:hover {
    border-color:#7c3aed; color:#7c3aed;
    background:rgba(124,58,237,0.05);
}
.kk-scope-active {
    border-color:#7c3aed !important; color:#7c3aed !important;
    background:rgba(124,58,237,0.08) !important;
}

/* Table rows */
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
</style>

<script>
// category_id → root_category_id map built from product rows
const productCategoryMap = {};

@foreach($products as $product)
productCategoryMap[{{ $product->id }}] = {
    rootId: {{ $product->category?->getRootId() ?? 'null' }},
    name: '{{ addslashes($product->name) }}'
};
@endforeach

function stocktakeForm() {
    return {
        selectedCategoryId: '',
        search: '',
        get visibleCount() {
            return document.querySelectorAll('.kk-row[style*="display: table-row"], .kk-row:not([style*="display: none"])').length;
        },
        init() {
            // default: all visible
        },
        isVisible(productId, productName) {
            const matchesScope = this.selectedCategoryId === '' ||
                productCategoryMap[productId]?.rootId == this.selectedCategoryId;
            const matchesSearch = this.search === '' ||
                productName.toLowerCase().includes(this.search.toLowerCase());
            return matchesScope && matchesSearch;
        },
        filterProducts() {
            // Alpine x-show handles reactivity
        }
    };
}
</script>
@endsection
