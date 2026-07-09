@extends('layouts.admin')

@section('title', $destination ? 'Kiểm kê ' . $destination->name : 'Tạo phiếu kiểm kê')
@section('page-title', $destination ? 'Kiểm kê ' . $destination->name : 'Tạo phiếu kiểm kê')
@section('breadcrumb', 'Kiểm kê / Tạo phiếu')

@section('content')

@if(session('error'))
<div class="mb-4 rounded-xl px-4 py-3 text-sm" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);color:#dc2626">
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 rounded-xl px-4 py-3 text-sm" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);color:#dc2626">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<script>
    window.__stocktakeProductsData = @json($productsData);
    window.stocktakeForm = function(productsData) {
        return {
            productsData,
            rows: [{ id: 1, product_id: '', actual_qty: '' }],
            nextId: 2,
            selectedCategoryId: '{{ old('category_id', '') }}',
            submittingButton: null,
            submitting: false,
            palette: { open: false, search: '', results: [], targetRowId: null, focusIdx: -1 },

            addRow() {
                this.rows.push({ id: this.nextId++, product_id: '', actual_qty: '' });
            },
            removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
            productOf(row) { return this.productsData[row.product_id]; },

            // ── Command Palette ───────────────────────────────────────────────
            _selectedIds(excludeRowId) {
                return new Set(this.rows.filter(r => r.id !== excludeRowId && r.product_id).map(r => parseInt(r.product_id)));
            },
            _allProducts() {
                return Object.entries(this.productsData).map(([id, p]) => ({ id: parseInt(id), ...p }));
            },
            _filteredProducts(row) {
                const q = this.palette.search.toLowerCase().trim();
                const taken = this._selectedIds(row ? row.id : null);
                return this._allProducts().filter(p => {
                    if (taken.has(p.id)) return false;
                    if (this.selectedCategoryId !== '' && p.rootId != this.selectedCategoryId) return false;
                    if (!q) return true;
                    return p.name.toLowerCase().includes(q) ||
                        (p.sku || '').toLowerCase().includes(q) ||
                        (p.category || '').toLowerCase().includes(q);
                });
            },
            openPalette(row) {
                this.palette.targetRowId = row.id;
                this.palette.search = '';
                this.palette.focusIdx = -1;
                this.palette.results = this._filteredProducts(row);
                this.palette.open = true;
                this.$nextTick(() => {
                    document.getElementById('cp-search')?.focus();
                    document.getElementById('cp-list')?.scrollTo(0, 0);
                });
            },
            closePalette() {
                this.palette.open = false;
                this.palette.targetRowId = null;
            },
            filterPalette() {
                const row = this.rows.find(r => r.id === this.palette.targetRowId);
                this.palette.results = this._filteredProducts(row);
                this.palette.focusIdx = this.palette.results.length ? 0 : -1;
            },
            selectFromPalette(product) {
                const row = this.rows.find(r => r.id === this.palette.targetRowId);
                if (row) { row.product_id = product.id; }
                this.closePalette();
            },
            paletteMoveDown() {
                if (this.palette.focusIdx < this.palette.results.length - 1) {
                    this.palette.focusIdx++;
                    this.$nextTick(() => document.querySelector('.cp-focused')?.scrollIntoView({ block: 'nearest' }));
                }
            },
            paletteMoveUp() {
                if (this.palette.focusIdx > 0) {
                    this.palette.focusIdx--;
                    this.$nextTick(() => document.querySelector('.cp-focused')?.scrollIntoView({ block: 'nearest' }));
                }
            },
            paletteConfirm() {
                const item = this.palette.results[this.palette.focusIdx] ?? (this.palette.results.length === 1 ? this.palette.results[0] : null);
                if (item) this.selectFromPalette(item);
            },

            formatQty(n) { return new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 3 }).format(n || 0); },

            // ── Submit ────────────────────────────────────────────────────────
            handleSubmit() {
                if (this.submitting) return;
                this.submitting = true;
                // Bỏ các dòng chưa chọn sản phẩm trước khi gửi
                this.rows = this.rows.filter(r => r.product_id);
                this.$nextTick(() => {
                    const form = this.$refs.form;
                    const fd = new FormData(form);
                    if (this.submittingButton) fd.append(this.submittingButton, '1');
                    fetch(form.action, { method: 'POST', body: fd })
                        .then(resp => { window.location.href = resp.url; })
                        .catch(() => { this.submitting = false; });
                });
            },
        };
    };
</script>

<div x-data="stocktakeForm(window.__stocktakeProductsData)">
<form x-ref="form" action="{{ route('stocktakes.store') }}" method="POST" @submit.prevent="handleSubmit()">
    @csrf

    @if($destination)
    <input type="hidden" name="destination_id" value="{{ $destination->id }}">
    @endif

    {{-- ── Action bar ──────────────────────────────────────────────── --}}
    <div class="rounded-2xl px-4 py-3 mb-4" style="background:var(--surface-card);border:1px solid var(--surface-border);box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
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
                <button type="submit" @click="submittingButton = 'save'" :disabled="submitting" class="btn-outline">
                    <i class="bi bi-floppy" style="font-size:12px"></i> Lưu nháp
                </button>
                <button type="submit" @click="submittingButton = 'submit'" :disabled="submitting" class="{{ $destination ? 'btn-primary-blue' : 'btn-primary' }}">
                    <template x-if="submitting">
                        <i class="bi bi-hourglass-split" style="font-size:11px"></i>
                    </template>
                    <template x-if="!submitting">
                        <i class="bi bi-send" style="font-size:11px"></i>
                    </template>
                    Gửi chờ duyệt
                </button>
            </div>
        </div>

        {{-- Category scope selector — chỉ hiện ở chế độ Kho Tổng --}}
        @if(!$destination)
        <div class="mt-3 pt-3 flex items-center gap-2 flex-wrap" style="border-top:1px solid var(--surface-border)">
            <span class="text-xs font-semibold uppercase tracking-wide flex-shrink-0" style="color:var(--text-muted)">Phạm vi:</span>
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
            <span class="text-xs" style="color:var(--text-muted); margin-left:4px">— giới hạn danh sách chọn sản phẩm bên dưới</span>
        </div>
        @endif
    </div>

    {{-- ── Product table ────────────────────────────────────────────── --}}
    <div class="create-card">

        {{-- Toolbar --}}
        <div class="create-card-header" style="gap:10px; padding:10px 16px">
            <div class="create-accent-bar" style="background:{{ $destination ? '#2563eb' : '#7c3aed' }}; flex-shrink:0"></div>
            <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">Sản phẩm kiểm kê</span>
            <span style="font-size:11px; padding:2px 8px; border-radius:99px; background:var(--surface-bg); color:var(--text-muted)">
                <span x-text="rows.length"></span> dòng
            </span>
            <button type="button" @click="addRow()" class="btn-add-row" style="margin-left:auto">
                <i class="bi bi-plus-lg" style="font-size:10px"></i> Thêm dòng
            </button>
        </div>

        {{-- Hint row --}}
        <p style="padding:7px 16px; font-size:12px; color:var(--text-muted); border-bottom:1px solid var(--surface-border)">
            @if($destination)
                Chọn sản phẩm cần kiểm kê tại <strong>{{ $destination->name }}</strong> rồi nhập SL thực tế.
                <span style="color:#2563eb">Tồn HT = tổng đã xuất đến kho này.</span>
            @else
                Chọn sản phẩm cần kiểm kê rồi nhập số lượng thực tế đếm được. Dòng chưa chọn sản phẩm sẽ tự bỏ qua.
            @endif
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead style="background:var(--surface-bg); border-bottom:1px solid var(--surface-border)">
                    <tr>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:44px">#</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em">Sản phẩm</th>
                        <th style="padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:80px">ĐVT</th>
                        <th style="padding:10px 12px; text-align:right; font-size:11px; font-weight:600; color:{{ $destination ? '#2563eb' : 'var(--text-muted)' }}; text-transform:uppercase; letter-spacing:.05em; width:140px">
                            {{ $destination ? 'Đã nhận (HT)' : 'Tồn hệ thống' }}
                        </th>
                        <th style="padding:10px 12px; text-align:right; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; width:160px">SL thực tế</th>
                        <th style="padding:10px 12px; width:40px"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, i) in rows" :key="row.id">
                        <tr class="group/row kk-row" style="border-top:1px solid var(--surface-border)">
                            <td style="padding:10px 16px">
                                <span class="kk-row-num" x-text="i + 1"></span>
                            </td>

                            {{-- Product picker (command palette trigger) --}}
                            <td style="padding:10px 12px">
                                <input type="hidden" :name="`details[${i}][product_id]`" :value="row.product_id">
                                <button type="button" @click="openPalette(row)"
                                        class="create-table-select w-full flex items-center justify-between gap-2 text-left">
                                    <span class="truncate flex-1"
                                          :style="row.product_id ? 'color:var(--text-primary)' : 'color:var(--text-muted)'"
                                          x-text="row.product_id && productOf(row) ? productOf(row).name : 'Chọn sản phẩm...'">
                                    </span>
                                    <i class="bi bi-search flex-shrink-0" style="font-size:11px; color:var(--text-muted); opacity:.5"></i>
                                </button>
                            </td>

                            <td style="padding:10px 12px; color:var(--text-secondary); font-size:13px"
                                x-text="row.product_id && productOf(row) ? productOf(row).unitName : '—'"></td>

                            <td style="padding:10px 12px; text-align:right; font-size:13.5px; font-variant-numeric:tabular-nums;
                                       color:{{ $destination ? '#2563eb' : 'var(--text-secondary)' }}; font-weight:{{ $destination ? '600' : '400' }}">
                                <input type="hidden" :name="`details[${i}][system_qty]`" :value="row.product_id && productOf(row) ? productOf(row).systemQty : 0">
                                <span x-text="row.product_id && productOf(row) ? formatQty(productOf(row).systemQty) : '—'"></span>
                            </td>

                            <td style="padding:8px 12px; text-align:right">
                                <input type="number" :name="`details[${i}][actual_qty]`" x-model="row.actual_qty"
                                       min="0" step="0.001" placeholder="—" :disabled="!row.product_id"
                                       class="kk-qty-input {{ $destination ? 'kk-qty-blue' : '' }}">
                            </td>

                            <td style="padding:10px 12px; text-align:center">
                                <button type="button" @click="removeRow(i)" class="create-delete-btn">
                                    <i class="bi bi-x" style="font-size:14px; line-height:1"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="rows.length === 0" style="padding:48px 20px; text-align:center; color:var(--text-muted)">
            <i class="ph ph-package" style="font-size:40px; display:block; margin-bottom:10px; opacity:.4"></i>
            <p style="font-size:14px">Chưa có sản phẩm nào trong phiếu kiểm kê.</p>
            <p style="font-size:12px; margin-top:4px">Bấm "Thêm dòng" để chọn sản phẩm.</p>
        </div>
    </div>

    {{-- Command Palette --}}
    <div x-show="palette.open"
         @keydown.escape.window="if(palette.open) { closePalette(); $event.stopPropagation(); }"
         @keydown.arrow-down.window.prevent="if(palette.open) paletteMoveDown()"
         @keydown.arrow-up.window.prevent="if(palette.open) paletteMoveUp()"
         @keydown.enter.window.prevent="if(palette.open) paletteConfirm()"
         class="cp-overlay" style="display:none">

        <div class="cp-backdrop" @click="closePalette()"></div>

        <div class="cp-panel">
            {{-- Search --}}
            <div class="cp-searchbar">
                <i class="bi bi-search"></i>
                <input id="cp-search" class="cp-search-input"
                       type="text" autocomplete="off"
                       placeholder="Tìm theo tên, SKU, danh mục..."
                       x-model="palette.search"
                       @input="filterPalette()">
                <span class="cp-kbd">ESC</span>
            </div>

            {{-- Results --}}
            <div id="cp-list" class="cp-list">
                <template x-if="palette.results.length === 0">
                    <div class="cp-empty">
                        <i class="bi bi-search"></i>
                        Không tìm thấy sản phẩm
                    </div>
                </template>
                <template x-for="(p, idx) in palette.results" :key="p.id">
                    <button type="button" class="cp-item"
                            :class="palette.focusIdx === idx ? 'cp-focused' : ''"
                            @click="selectFromPalette(p)"
                            @mouseenter="palette.focusIdx = idx">
                        <div class="cp-item-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="cp-item-body">
                            <div class="cp-item-top">
                                <span class="cp-item-name" x-text="p.name"></span>
                                <span class="cp-item-sku" x-show="p.sku" x-text="p.sku"></span>
                            </div>
                            <div class="cp-item-cat" x-show="p.category" x-text="p.category"></div>
                        </div>
                        <div class="cp-item-stock">
                            <div class="cp-item-qty" x-text="formatQty(p.systemQty)"></div>
                            <div class="cp-item-unit" x-text="p.unitName"></div>
                        </div>
                    </button>
                </template>
            </div>

            {{-- Footer --}}
            <div class="cp-footer">
                <span class="cp-count"><span x-text="palette.results.length"></span> sản phẩm</span>
                <div class="cp-hints">
                    <span class="cp-hint"><kbd>↑↓</kbd> di chuyển</span>
                    <span class="cp-hint"><kbd>↵</kbd> chọn</span>
                    <span class="cp-hint"><kbd>ESC</kbd> đóng</span>
                </div>
            </div>
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
.btn-add-row {
    font-size:12px; font-weight:600; padding:5px 12px; border-radius:7px;
    background:rgba(124,58,237,.08); color:#7c3aed; border:1px solid rgba(124,58,237,.2);
    cursor:pointer; transition:background .15s; display:inline-flex; align-items:center; gap:5px;
}
.btn-add-row:hover { background:rgba(124,58,237,.15); }
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
.kk-qty-input:disabled { opacity:.5; cursor:not-allowed; }
.kk-qty-blue:focus { border-color:#2563eb !important; }
.kk-qty-blue:not(:placeholder-shown) {
    border-color:#2563eb !important; background:rgba(37,99,235,0.04) !important;
    color:#2563eb !important;
}
.create-table-select {
    width:100%; background:transparent; border:none; border-bottom:1.5px solid var(--surface-border);
    color:var(--text-primary); font-size:13.5px; padding:4px 2px 5px; outline:none;
    transition:border-color .15s; border-radius:0; cursor:pointer; min-height:29px;
}
.create-table-select:focus { border-bottom-color:#7c3aed; }
.dark .create-table-select { background:transparent !important; border-color:var(--surface-border) !important; color:var(--text-primary) !important; }
.create-delete-btn {
    width:24px; height:24px; border-radius:6px; border:none; display:flex;
    align-items:center; justify-content:center; background:transparent; color:var(--text-muted);
    cursor:pointer; opacity:0; transition:opacity .15s, background .15s, color .15s; margin:0 auto;
}
.kk-row:hover .create-delete-btn { opacity:1; }
.create-delete-btn:hover { background:#fee2e2; color:#dc2626; }
.dark .create-delete-btn:hover { background:rgba(220,38,38,.2); }
</style>

@endsection
