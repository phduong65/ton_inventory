@extends('layouts.admin')

@section('title', $type === 'IN' ? 'Tạo phiếu nhập' : 'Tạo phiếu xuất')
@section('page-title', $type === 'IN' ? 'Tạo phiếu nhập kho' : 'Tạo phiếu xuất kho')
@section('breadcrumb', 'Phiếu NK/XK / ' . ($type === 'IN' ? 'Nhập kho' : 'Xuất kho'))

@php $requireApproval = \App\Models\Setting::get('require_approval', true); @endphp

@section('content')
{{-- Định nghĩa functions và data TRƯỚC khi Alpine gặp x-data --}}
<script>
    window.__txFormData = @json($productsUnitData);
    window.__quickProductUrl  = '{{ route("quick.products.store") }}';
    window.__quickSupplierUrl = '{{ route("quick.suppliers.store") }}';
    window.createForm = function(productsData) {
        return {
            productsData,
            type: '{{ $type }}',
            showStockDialog: false,
            stockError: '',
            submittingButton: null,
            submitting: false,
            rows: [{ id: 1, product_id: '', unit_id: '', conversion_factor: 1, baseUnitName: '', availableUnits: [], availableStock: 0, qty: 1, price: 0, discount: 0, vat: 0, discountAmt: 0, vatAmt: 0, amount: 0 }],
            nextId: 2,
            palette: { open: false, search: '', results: [], targetRowId: null, focusIdx: -1 },
            get total() { return this.rows.reduce((s, r) => s + (r.amount || 0), 0); },
            get totalDiscount() { return this.rows.reduce((s, r) => s + (r.discountAmt || 0), 0); },
            get totalVat() { return this.rows.reduce((s, r) => s + (r.vatAmt || 0), 0); },
            addRow() {
                this.rows.push({ id: this.nextId++, product_id: '', unit_id: '', conversion_factor: 1, baseUnitName: '', availableUnits: [], availableStock: 0, qty: 1, price: 0, discount: 0, vat: 0, discountAmt: 0, vatAmt: 0, amount: 0 });
            },
            removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
            onProductChange(row) {
                const p = this.productsData[row.product_id];
                if (!p) { row.availableUnits = []; row.unit_id = ''; row.conversion_factor = 1; row.baseUnitName = ''; row.availableStock = 0; return; }
                const units = [{ unitId: p.baseUnitId, unitName: p.baseUnitName, factor: 1 }];
                (p.conversions || []).forEach(c => units.push(c));
                row.baseUnitName = p.baseUnitName;
                row.availableStock = p.stock ?? 0;
                if (p.defaultPrice && !row.price) row.price = p.defaultPrice;
                row.unit_id = ''; row.conversion_factor = 1; row.availableUnits = units;
                this.$nextTick(() => { row.unit_id = p.baseUnitId; this.calcRow(row); });
            },
            onUnitChange(row) {
                const p = this.productsData[row.product_id];
                if (!p) return;
                if (parseInt(row.unit_id) === p.baseUnitId) { row.conversion_factor = 1; }
                else { const conv = (p.conversions || []).find(c => c.unitId == row.unit_id); row.conversion_factor = conv ? conv.factor : 1; }
                this.calcRow(row);
            },
            calcRow(row) {
                const qty = parseFloat(row.qty) || 0, price = parseFloat(row.price) || 0,
                      ck  = parseFloat(row.discount) || 0, vat = parseFloat(row.vat) || 0;
                const baseAmt = qty * price;
                row.discountAmt = baseAmt * (ck / 100);
                row.vatAmt = (baseAmt - row.discountAmt) * (vat / 100);
                row.amount = baseAmt - row.discountAmt + row.vatAmt;
            },
            validateQty(row, el) {
                if (this.type !== 'OUT' || !row.product_id) return;
                const baseQty = (parseFloat(row.qty) || 0) * (parseFloat(row.conversion_factor) || 1);
                const stock   = row.availableStock;
                if (baseQty > stock) {
                    const unit = row.baseUnitName || '';
                    this.stockError = `Số lượng xuất (${this.formatQty(baseQty)} ${unit}) vượt quá tồn kho hiện tại (${this.formatQty(stock)} ${unit}).`;
                    this.showStockDialog = true;
                    this.$nextTick(() => el.focus());
                }
            },
            handleSubmit(event) {
                // 1. Kiểm tra tồn kho cho xuất kho
                if (this.type === 'OUT') {
                    for (let i = 0; i < this.rows.length; i++) {
                        const row = this.rows[i];
                        if (!row.product_id) continue;
                        const baseQty = (parseFloat(row.qty) || 0) * (parseFloat(row.conversion_factor) || 1);
                        if (baseQty > row.availableStock) {
                            const unit = row.baseUnitName || '';
                            this.stockError = `Dòng ${i + 1}: Số lượng xuất (${this.formatQty(baseQty)} ${unit}) vượt quá tồn kho (${this.formatQty(row.availableStock)} ${unit}).`;
                            this.showStockDialog = true;
                            this.$nextTick(() => {
                                document.querySelector(`input[name="details[${i}][qty]"]`)?.focus();
                            });
                            return; // dừng, không submit
                        }
                    }
                }

                if (this.submitting) return;
                this.submitting = true;

                // 2. Build FormData — dùng fetch để đính kèm files từ Alpine state
                // (native submit không reliable vì file input đã bị clear sau khi preview)
                const form = this.$el;
                const fd = new FormData(form);

                // Xóa file inputs rỗng (đã bị clear bởi handleFiles), thêm files thực từ previews
                fd.delete('images[]');
                const uploads = window.__imgUploads;
                if (uploads) {
                    uploads.previews.forEach(p => fd.append('images[]', p.file, p.name));
                }

                // Thêm tên button (save/submit) vào FormData
                if (this.submittingButton) fd.append(this.submittingButton, '1');

                fetch(form.action, { method: 'POST', body: fd })
                    .then(resp => { window.location.href = resp.url; })
                    .catch(() => { this.submitting = false; });
            },
            formatQty(n) { return new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 3 }).format(n); },
            formatNum(n) { return new Intl.NumberFormat('vi-VN').format(Math.round(n)) + 'đ'; },

            // ── Command Palette ───────────────────────────────────────────────
            _allProducts() {
                return Object.entries(this.productsData).map(([id, p]) => ({ id: parseInt(id), ...p }));
            },
            openPalette(row) {
                this.palette.targetRowId = row.id;
                this.palette.search = '';
                this.palette.focusIdx = -1;
                this.palette.results = this._allProducts();
                this.palette.open = true;
                this.$nextTick(() => {
                    const el = document.getElementById('cp-search');
                    if (el) { el.focus(); }
                    document.getElementById('cp-list')?.scrollTo(0, 0);
                });
            },
            closePalette() {
                this.palette.open = false;
                this.palette.targetRowId = null;
            },
            filterPalette() {
                const q = this.palette.search.toLowerCase().trim();
                const all = this._allProducts();
                this.palette.results = q
                    ? all.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.sku || '').toLowerCase().includes(q) ||
                        (p.category || '').toLowerCase().includes(q))
                    : all;
                this.palette.focusIdx = this.palette.results.length ? 0 : -1;
            },
            selectFromPalette(product) {
                const row = this.rows.find(r => r.id === this.palette.targetRowId);
                if (row) { row.product_id = product.id; this.onProductChange(row); }
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

            // ── Quick-add ─────────────────────────────────────────────────────
            quickProduct:  { open: false, saving: false, name: '', sku: '', unit_id: '', category_id: '', default_price: '', targetRowId: null, errors: {} },
            quickSupplier: { open: false, saving: false, name: '', code: '', phone: '', email: '', errors: {} },

            openQuickProduct() {
                const term      = this.palette.search.trim();
                const targetId  = this.palette.targetRowId;
                this.closePalette();
                this.quickProduct.name          = term;
                this.quickProduct.targetRowId   = targetId;
                this.quickProduct.sku           = '';
                this.quickProduct.unit_id       = '';
                this.quickProduct.category_id   = '';
                this.quickProduct.default_price = '';
                this.quickProduct.errors        = {};
                this.quickProduct.open          = true;
            },
            saveQuickProduct() {
                if (this.quickProduct.saving) return;
                this.quickProduct.saving = true;
                this.quickProduct.errors = {};
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                fetch(window.__quickProductUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        name:          this.quickProduct.name,
                        sku:           this.quickProduct.sku          || null,
                        unit_id:       this.quickProduct.unit_id      || null,
                        category_id:   this.quickProduct.category_id  || null,
                        default_price: this.quickProduct.default_price || null,
                    }),
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok) {
                        this.productsData[data.id] = data;
                        const rowId = this.quickProduct.targetRowId;
                        this.quickProduct.open = false;
                        this.$nextTick(() => {
                            const row = this.rows.find(r => r.id === rowId);
                            if (row) {
                                row.product_id = data.id;
                                this.onProductChange(row);
                                this.$nextTick(() => {
                                    const idx = this.rows.findIndex(r => r.id === rowId);
                                    const el  = document.querySelector(`input[name="details[${idx}][qty]"]`);
                                    if (el) { el.focus(); el.select(); }
                                });
                            }
                        });
                    } else {
                        this.quickProduct.errors = data.errors || {};
                    }
                    this.quickProduct.saving = false;
                })
                .catch(() => { this.quickProduct.saving = false; });
            },
            openQuickSupplier() {
                this.quickSupplier.name   = '';
                this.quickSupplier.code   = '';
                this.quickSupplier.phone  = '';
                this.quickSupplier.email  = '';
                this.quickSupplier.errors = {};
                this.quickSupplier.open   = true;
            },
            saveQuickSupplier() {
                if (this.quickSupplier.saving) return;
                this.quickSupplier.saving = true;
                this.quickSupplier.errors = {};
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                fetch(window.__quickSupplierUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        name:  this.quickSupplier.name,
                        code:  this.quickSupplier.code  || null,
                        phone: this.quickSupplier.phone || null,
                        email: this.quickSupplier.email || null,
                    }),
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok) {
                        const sel = document.getElementById('supplier-select');
                        if (sel) {
                            const opt = new Option(data.name, data.id, true, true);
                            sel.add(opt);
                        }
                        this.quickSupplier.open = false;
                    } else {
                        this.quickSupplier.errors = data.errors || {};
                    }
                    this.quickSupplier.saving = false;
                })
                .catch(() => { this.quickSupplier.saving = false; });
            },
        };
    };
    window.imageUpload = function() {
        return {
            dragging: false, previews: [], _uid: 0, lb: { open: false, current: 0 },
            init() { window.__imgUploads = this; },
            openLightbox(i) { this.lb.current = i; this.lb.open = true; },
            lbPrev() { this.lb.current = (this.lb.current - 1 + this.previews.length) % this.previews.length; },
            lbNext() { this.lb.current = (this.lb.current + 1) % this.previews.length; },
            handleFiles(e) { this.addFiles([...e.target.files]); e.target.value = ''; },
            handleDrop(e) { this.dragging = false; this.addFiles([...e.dataTransfer.files].filter(f => f.type.startsWith('image/'))); },
            addFiles(files) {
                const rem = 10 - this.previews.length;
                files.slice(0, rem).forEach(file => {
                    if (file.size > 5 * 1024 * 1024) { alert(`${file.name} vượt quá 5MB`); return; }
                    const kb = file.size / 1024, size = kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : Math.round(kb) + ' KB';
                    const reader = new FileReader();
                    reader.onload = e => { this.previews.push({ id: ++this._uid, url: e.target.result, name: file.name, size, file }); };
                    reader.readAsDataURL(file);
                });
            },
            removePreview(i) {
                if (this.lb.open && this.lb.current >= this.previews.length - 1) this.lb.current = Math.max(0, this.previews.length - 2);
                this.previews.splice(i, 1);
                if (this.previews.length === 0) this.lb.open = false;
            },
        };
    };
</script>

<form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data" x-data="createForm(window.__txFormData)" @submit.prevent="handleSubmit($event)">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- Action bar --}}
        <div class="create-action-bar">
            {{-- Left: back + title --}}
            <div class="create-action-left">
                <a href="{{ route('transactions.index') }}" class="btn-icon" title="Quay lại">
                    <i class="bi bi-arrow-left" style="font-size:13px"></i>
                </a>
                <span class="type-badge {{ $type === 'IN' ? 'type-in' : 'type-out' }}">
                    {{ $type === 'IN' ? 'Nhập kho' : 'Xuất kho' }}
                </span>
                <span class="create-action-subtitle">/ Phiếu mới</span>
            </div>
            {{-- Right: action buttons --}}
            <div class="create-action-right">
                <a href="{{ route('transactions.index') }}" class="btn-ghost">Hủy</a>
                <button type="submit" @click="submittingButton = 'save'" :disabled="submitting" class="btn-outline">
                    <i class="bi bi-floppy" style="font-size:12px"></i>
                    <span class="btn-label-full">Lưu nháp</span>
                    <span class="btn-label-short">Nháp</span>
                </button>
                <button type="submit" @click="submittingButton = 'submit'" :disabled="submitting" class="btn-primary">
                    <template x-if="submitting">
                        <i class="bi bi-hourglass-split" style="font-size:11px"></i>
                    </template>
                    <template x-if="!submitting">
                        @if($requireApproval)
                            <i class="bi bi-send" style="font-size:11px"></i>
                        @else
                            <i class="bi bi-check-circle" style="font-size:11px"></i>
                        @endif
                    </template>
                    <span class="btn-label-full">{{ $requireApproval ? 'Lưu & Gửi duyệt' : 'Lưu & Xác nhận' }}</span>
                    <span class="btn-label-short">{{ $requireApproval ? 'Gửi duyệt' : 'Xác nhận' }}</span>
                </button>
            </div>
        </div>

        {{-- Single column layout --}}
        <div style="display:flex; flex-direction:column; gap:16px">

            {{-- Info panel --}}
            <div class="create-card">
                <div class="create-card-header">
                    <div class="create-accent-bar"></div>
                    <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">Thông tin phiếu</span>
                </div>
                <div style="padding:16px; display:flex; flex-direction:column; gap:12px">
                    {{-- Row 1: Ngày + NCC/Điểm nhận --}}
                    <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap">
                        <div style="flex:1; min-width:160px">
                            <label class="create-label">
                                Ngày {{ $type === 'IN' ? 'nhập' : 'xuất' }}
                                <span style="color:#ef4444">*</span>
                            </label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                                class="create-sidebar-input">
                        </div>
                        @if ($type === 'IN')
                            <div style="flex:2; min-width:200px">
                                <label class="create-label">Nhà cung cấp <span style="color:#ef4444">*</span></label>
                                <div style="display:flex; gap:6px; align-items:stretch">
                                    <select id="supplier-select" name="supplier_id" required class="create-sidebar-input" style="flex:1; min-width:0">
                                        <option value="">Chọn nhà cung cấp...</option>
                                        @foreach ($suppliers as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create-suppliers')
                                    <button type="button" @click="openQuickSupplier()" title="Thêm nhà cung cấp mới" class="quick-add-btn">
                                        <i class="bi bi-plus-lg" style="font-size:13px"></i>
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        @else
                            <div style="flex:2; min-width:200px">
                                <label class="create-label">Điểm nhận <span style="color:#ef4444">*</span></label>
                                <select name="destination_id" required class="create-sidebar-input">
                                    <option value="">Chọn kho nhận...</option>
                                    @foreach ($destinations as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    {{-- Row 2: Ghi chú full width --}}
                    <div>
                        <label class="create-label">Ghi chú</label>
                        <input type="text" name="note" placeholder="Ghi chú về lô hàng..."
                            class="create-sidebar-input">
                    </div>
                </div>
            </div>

            {{-- Product table --}}
            <div class="create-card">
                {{-- Table toolbar --}}
                <div class="create-card-header">
                    <div class="create-accent-bar"></div>
                    <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">
                        Chi tiết {{ $type === 'IN' ? 'hàng nhập' : 'hàng xuất' }}
                    </span>
                    <span
                        style="font-size:11px; padding:2px 8px; border-radius:99px; background:var(--surface-bg); color:var(--text-muted)">
                        <span x-text="rows.length"></span> dòng
                    </span>
                    <button type="button" @click="addRow()" class="btn-add-row" style="margin-left:auto">
                        <i class="bi bi-plus-lg" style="font-size:10px"></i> Thêm dòng
                    </button>
                </div>

                {{-- Table --}}
                <div style="overflow-x:auto; -webkit-overflow-scrolling:touch">
                    <table style="width:100%; min-width:{{ $type === 'IN' ? '820px' : '560px' }}; font-size:13.5px; border-collapse:collapse">
                        <thead>
                            <tr style="background:var(--surface-bg); border-bottom:1px solid var(--surface-border)">
                                <th class="px-4 py-2.5 text-left"
                                    style="width:36px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">#
                                </th>
                                <th class="px-3 py-2.5 text-left"
                                    style="min-width:180px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                    SẢN PHẨM</th>
                                <th class="px-3 py-2.5 text-left"
                                    style="width:90px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                    ĐƠN VỊ</th>
                                <th class="px-3 py-2.5 text-right"
                                    style="width:90px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                    SỐ LƯỢNG</th>
                                <th class="px-3 py-2.5 text-right"
                                    style="width:96px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                    QUY ĐỔI</th>
                                @if ($type === 'IN')
                                    <th class="px-3 py-2.5 text-right"
                                        style="width:100px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                        ĐƠN GIÁ</th>
                                    <th class="px-3 py-2.5 text-right"
                                        style="width:58px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                        CK%</th>
                                    <th class="px-3 py-2.5 text-right"
                                        style="width:58px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                        VAT%</th>
                                    <th class="px-3 py-2.5 text-right"
                                        style="width:110px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em; white-space:nowrap">
                                        THÀNH TIỀN</th>
                                @endif
                                <th class="px-3 py-2.5"
                                    style="width:40px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in rows" :key="row.id">
                                <tr class="group/row create-row" style="border-top:1px solid var(--surface-border)">

                                    {{-- Row number --}}
                                    <td class="px-4 py-3">
                                        <span class="create-row-num" x-text="i + 1"></span>
                                    </td>

                                    {{-- Product picker (command palette trigger) --}}
                                    <td class="px-3 py-2.5">
                                        <input type="hidden" :name="`details[${i}][product_id]`" :value="row.product_id">
                                        <button type="button" @click="openPalette(row)"
                                            class="create-table-select w-full flex items-center justify-between gap-2 text-left">
                                            <span class="truncate flex-1"
                                                  :style="row.product_id ? 'color:var(--text-primary)' : 'color:var(--text-muted)'"
                                                  x-text="row.product_id && productsData[row.product_id] ? productsData[row.product_id].name : 'Chọn sản phẩm...'">
                                            </span>
                                            <i class="bi bi-search flex-shrink-0" style="font-size:11px; color:var(--text-muted); opacity:.5"></i>
                                        </button>
                                    </td>

                                    {{-- Unit select --}}
                                    <td class="px-3 py-2.5">
                                        <select :name="`details[${i}][unit_id]`" x-model="row.unit_id"
                                            @change="onUnitChange(row)" :disabled="!row.product_id" required
                                            class="create-table-select">
                                            <option value="">ĐVT...</option>
                                            <template x-for="u in row.availableUnits" :key="u.unitId">
                                                <option :value="u.unitId" x-text="u.unitName"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" :name="`details[${i}][conversion_factor]`"
                                            :value="row.conversion_factor">
                                    </td>

                                    {{-- Qty --}}
                                    <td class="px-3 py-2.5">
                                        <input type="number" :name="`details[${i}][qty]`" x-model="row.qty"
                                            @input="calcRow(row)" @change="validateQty(row, $el)"
                                            min="0.001" step="any" required
                                            class="create-table-input text-right">
                                    </td>

                                    {{-- Base qty display --}}
                                    <td class="px-3 py-2.5 text-right tabular-nums"
                                        style="font-size:12px; color:var(--text-muted)">
                                        <span x-show="row.conversion_factor > 1"
                                            x-text="formatQty(row.qty * row.conversion_factor) + ' ' + row.baseUnitName"
                                            style="white-space:nowrap"></span>
                                        <span x-show="row.conversion_factor <= 1"
                                            style="color:var(--surface-border)">—</span>
                                    </td>

                                    @if ($type === 'IN')
                                        {{-- Unit price --}}
                                        <td class="px-3 py-2.5">
                                            <input type="number" :name="`details[${i}][price]`" x-model="row.price"
                                                @input="calcRow(row)" min="0" step="1000"
                                                class="create-table-input text-right">
                                        </td>

                                        {{-- Discount % --}}
                                        <td class="px-3 py-2.5">
                                            <input type="number" :name="`details[${i}][discount]`"
                                                x-model="row.discount" @input="calcRow(row)" min="0"
                                                max="100" step="any" placeholder="0"
                                                class="create-table-input text-right">
                                        </td>

                                        {{-- VAT % --}}
                                        <td class="px-3 py-2.5">
                                            <input type="number" :name="`details[${i}][vat]`" x-model="row.vat"
                                                @input="calcRow(row)" min="0" max="100" step="1"
                                                placeholder="0" class="create-table-input text-right">
                                        </td>

                                        {{-- Amount --}}
                                        <td class="px-3 py-2.5 text-right font-semibold tabular-nums"
                                            style="color:var(--text-primary); font-size:13.5px"
                                            x-text="row.amount ? formatNum(row.amount) : '—'">
                                        </td>
                                    @endif

                                    {{-- Delete --}}
                                    <td class="px-3 py-2.5 text-center">
                                        <button type="button" @click="removeRow(i)" class="create-delete-btn">
                                            <i class="bi bi-x" style="font-size:14px; line-height:1"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>

                        @if ($type === 'IN')
                            <tfoot>
                                <tr style="border-top:1px dashed var(--surface-border)">
                                    <td colspan="8" class="px-5 py-2 text-right text-xs font-medium"
                                        style="color:var(--text-muted)">Tổng chiết khấu</td>
                                    <td class="px-5 py-2 text-right text-sm tabular-nums font-semibold" style="color:#f97316">
                                        {{-- <span x-show="totalDiscount > 0" x-text="'- ' + formatNum(totalDiscount)"></span> --}}
                                        <span x-show="totalDiscount <= 0" style="color:var(--surface-border)">—</span>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="px-5 py-2 text-right text-xs font-medium"
                                        style="color:var(--text-muted)">Tổng VAT</td>
                                    <td class="px-5 py-2 text-right text-sm tabular-nums font-semibold" style="color:#3b82f6">
                                        {{-- <span x-show="totalVat > 0" x-text="'+ ' + formatNum(totalVat)"></span> --}}
                                        <span x-show="totalVat <= 0" style="color:var(--surface-border)">—</span>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="10">
                                        <div class="flex items-center justify-between px-5 py-4"
                                            style="border-top:2px solid var(--surface-border); background:rgba(22,163,74,0.04)">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold uppercase tracking-widest"
                                                    style="color:var(--text-muted)">Tổng cộng</span>
                                                <span class="text-xs px-2 py-0.5 rounded"
                                                    style="background:var(--surface-bg); color:var(--text-muted)">
                                                    <span x-text="rows.length"></span> dòng
                                                </span>
                                            </div>
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-2xl font-bold tabular-nums"
                                                    style="color:#16a34a; letter-spacing:-.02em"
                                                    x-text="formatNum(total)"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Upload panel (IN only) --}}
            @if ($type === 'IN')
                <div class="create-card" x-data="imageUpload()" @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)">

                    <div class="create-card-header">
                        <div class="create-accent-bar"></div>
                        <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">Ảnh đính kèm</span>
                        <span x-show="previews.length > 0"
                            style="font-size:11px; padding:2px 8px; border-radius:99px; background:var(--surface-bg); color:var(--text-muted)">
                            <span x-text="previews.length"></span>/10
                        </span>
                        <span style="margin-left:auto; font-size:11px; color:var(--text-muted)">≤ 5MB/ảnh</span>
                    </div>

                    <label x-show="previews.length === 0" class="upload-dropzone" :class="dragging ? 'dragging' : ''"
                        style="margin:12px; padding:20px 24px; flex-direction:row; gap:16px; justify-content:center">
                        <input type="file" name="images[]" multiple accept="image/*" class="sr-only"
                            @change="handleFiles($event)">
                        <div class="upload-icon-ring">
                            <i class="bi bi-cloud-arrow-up" style="font-size:20px"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-center" style="color:var(--text-secondary)">
                                <span style="color:#16a34a; font-weight:600">Chọn ảnh</span> hoặc kéo vào đây
                            </p>
                            <p class="text-xs text-center mt-0.5" style="color:var(--text-muted)">JPG, PNG, WEBP · Tối đa
                                10 ảnh</p>
                        </div>
                    </label>

                    <div x-show="previews.length > 0" class="p-3">
                        <div class="upload-grid">
                            <template x-for="(img, i) in previews" :key="img.id">
                                <div class="upload-thumb">
                                    <img :src="img.url" alt="" @click="openLightbox(i)"
                                        style="cursor:zoom-in">
                                    <div class="upload-thumb-overlay">
                                        <button type="button" @click.stop="openLightbox(i)" class="upload-zoom-btn">
                                            <i class="bi bi-arrows-fullscreen" style="font-size:12px"></i>
                                        </button>
                                    </div>
                                    <button type="button" @click.stop="removePreview(i)" class="upload-delete-corner">
                                        <i class="bi bi-x" style="font-size:13px; font-weight:700"></i>
                                    </button>
                                    <span class="upload-size-badge" x-text="img.size"></span>
                                </div>
                            </template>
                            <label x-show="previews.length < 10" class="upload-add-cell"
                                :class="dragging ? 'dragging' : ''">
                                <input type="file" name="images[]" multiple accept="image/*" class="sr-only"
                                    @change="handleFiles($event)">
                                <i class="bi bi-plus" style="font-size:18px"></i>
                                <span>Thêm</span>
                            </label>
                        </div>
                    </div>

                    {{-- Lightbox --}}
                    <div x-show="lb.open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm"
                         @click.self="lb.open = false"
                         @keydown.escape.window="lb.open = false"
                         @keydown.arrow-left.window="lb.open && lbPrev()"
                         @keydown.arrow-right.window="lb.open && lbNext()"
                         style="display:none">

                        <button type="button" @click="lb.open = false"
                                class="absolute top-4 right-4 w-9 h-9 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                            <i class="bi bi-x-lg text-sm"></i>
                        </button>

                        <button type="button" x-show="previews.length > 1" @click="lbPrev()"
                                class="absolute left-4 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <div class="max-w-4xl max-h-[85vh] mx-16 flex flex-col items-center gap-3">
                            <template x-if="previews[lb.current]">
                                <img :src="previews[lb.current].url" :alt="previews[lb.current].name"
                                     class="max-h-[78vh] max-w-full object-contain rounded-lg shadow-2xl">
                            </template>
                            <div x-show="previews[lb.current]" class="flex items-center gap-3">
                                <p class="text-white/80 text-sm" x-text="previews[lb.current]?.name"></p>
                                <span class="text-white/40 text-xs" x-text="`${lb.current + 1} / ${previews.length}`"></span>
                            </div>
                        </div>

                        <button type="button" x-show="previews.length > 1" @click="lbNext()"
                                class="absolute right-4 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                        <div x-show="previews.length > 1" class="absolute bottom-4 flex gap-1.5">
                            <template x-for="(_, idx) in previews" :key="idx">
                                <button type="button" @click="lb.current = idx"
                                        :class="idx === lb.current ? 'w-4 bg-white' : 'w-1.5 bg-white/40 hover:bg-white/60'"
                                        class="h-1.5 rounded-full transition-all duration-200"></button>
                            </template>
                        </div>
                    </div>
                </div>
            @endif

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
                            @can('create-products')
                            <button type="button" @click="openQuickProduct()"
                                    style="margin-top:10px; font-size:12.5px; font-weight:600; padding:6px 14px; border-radius:8px; background:rgba(22,163,74,.1); color:#16a34a; border:1px solid rgba(22,163,74,.25); cursor:pointer; display:inline-flex; align-items:center; gap:5px">
                                <i class="bi bi-plus-lg" style="font-size:11px"></i>
                                Thêm "<span x-text="palette.search"></span>"
                            </button>
                            @endcan
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
                                <div class="cp-item-qty"
                                     :style="p.stock <= 0 ? 'color:#ef4444' : 'color:#16a34a'"
                                     x-text="new Intl.NumberFormat('vi-VN').format(p.stock)"></div>
                                <div class="cp-item-unit" x-text="p.baseUnitName"></div>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="cp-footer">
                    <span class="cp-count"><span x-text="palette.results.length"></span> sản phẩm</span>
                    <div class="cp-hints">
                        @can('create-products')
                        <button type="button" @click="openQuickProduct()" title="Thêm sản phẩm mới"
                                style="font-size:11.5px; font-weight:600; padding:3px 10px; border-radius:6px; background:rgba(22,163,74,.1); color:#16a34a; border:1px solid rgba(22,163,74,.2); cursor:pointer; display:inline-flex; align-items:center; gap:4px; line-height:1.6">
                            <i class="bi bi-plus-lg" style="font-size:10px"></i> Thêm mới
                        </button>
                        @endcan
                        <span class="cp-hint"><kbd>↑↓</kbd> di chuyển</span>
                        <span class="cp-hint"><kbd>↵</kbd> chọn</span>
                        <span class="cp-hint"><kbd>ESC</kbd> đóng</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick-add Product Modal --}}
        <div x-show="quickProduct.open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[300] flex items-center justify-center p-4"
             @keydown.escape.window="if(quickProduct.open) { quickProduct.open = false; $event.stopPropagation(); }"
             style="display:none">
            <div class="absolute inset-0 bg-black/50" @click="quickProduct.open = false"></div>
            <div class="relative rounded-xl shadow-2xl w-full max-w-md p-6" style="background:var(--surface-card); border:1px solid var(--surface-border)">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <div style="width:28px; height:28px; border-radius:8px; background:rgba(22,163,74,.12); display:flex; align-items:center; justify-content:center">
                            <i class="bi bi-box-seam" style="font-size:13px; color:#16a34a"></i>
                        </div>
                        <h3 style="font-size:15px; font-weight:700; color:var(--text-primary)">Thêm sản phẩm mới</h3>
                    </div>
                    <button type="button" @click="quickProduct.open = false"
                            style="width:28px; height:28px; border-radius:6px; border:none; background:var(--surface-bg); color:var(--text-muted); cursor:pointer; display:flex; align-items:center; justify-content:center">
                        <i class="bi bi-x-lg" style="font-size:12px"></i>
                    </button>
                </div>
                <div style="display:flex; flex-direction:column; gap:12px">
                    <div>
                        <label class="create-label">Tên sản phẩm <span style="color:#ef4444">*</span></label>
                        <input type="text" x-model="quickProduct.name" class="create-sidebar-input"
                               placeholder="Nhập tên sản phẩm..."
                               @keydown.enter.prevent="saveQuickProduct()">
                        <p x-show="quickProduct.errors.name" class="text-xs mt-1" style="color:#ef4444"
                           x-text="(quickProduct.errors.name||[])[0]"></p>
                    </div>
                    <div>
                        <label class="create-label">Đơn vị tính <span style="color:#ef4444">*</span></label>
                        <select x-model="quickProduct.unit_id" class="create-sidebar-input">
                            <option value="">Chọn đơn vị...</option>
                            @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="quickProduct.errors.unit_id" class="text-xs mt-1" style="color:#ef4444"
                           x-text="(quickProduct.errors.unit_id||[])[0]"></p>
                    </div>
                    <div>
                        <label class="create-label">Danh mục</label>
                        <select x-model="quickProduct.category_id" class="create-sidebar-input">
                            <option value="">Không có</option>
                            @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex; gap:12px">
                        <div style="flex:1">
                            <label class="create-label">SKU <span style="font-weight:400; color:var(--text-muted)">(tự tạo nếu trống)</span></label>
                            <input type="text" x-model="quickProduct.sku" class="create-sidebar-input" placeholder="VD: SP-001"
                                   @keydown.enter.prevent="saveQuickProduct()">
                            <p x-show="quickProduct.errors.sku" class="text-xs mt-1" style="color:#ef4444"
                               x-text="(quickProduct.errors.sku||[])[0]"></p>
                        </div>
                        <div style="flex:1">
                            <label class="create-label">Giá mặc định</label>
                            <input type="number" x-model="quickProduct.default_price" class="create-sidebar-input"
                                   min="0" step="1000" placeholder="0"
                                   @keydown.enter.prevent="saveQuickProduct()">
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px">
                    <button type="button" @click="quickProduct.open = false" class="btn-ghost">Hủy</button>
                    <button type="button" @click="saveQuickProduct()" :disabled="quickProduct.saving" class="btn-primary">
                        <template x-if="quickProduct.saving"><i class="bi bi-hourglass-split" style="font-size:11px"></i></template>
                        <template x-if="!quickProduct.saving"><i class="bi bi-plus-lg" style="font-size:11px"></i></template>
                        <span x-text="quickProduct.saving ? 'Đang lưu...' : 'Thêm sản phẩm'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Quick-add Supplier Modal --}}
        <div x-show="quickSupplier.open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[300] flex items-center justify-center p-4"
             @keydown.escape.window="if(quickSupplier.open) { quickSupplier.open = false; $event.stopPropagation(); }"
             style="display:none">
            <div class="absolute inset-0 bg-black/50" @click="quickSupplier.open = false"></div>
            <div class="relative rounded-xl shadow-2xl w-full max-w-md p-6" style="background:var(--surface-card); border:1px solid var(--surface-border)">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <div style="width:28px; height:28px; border-radius:8px; background:rgba(59,130,246,.12); display:flex; align-items:center; justify-content:center">
                            <i class="bi bi-building" style="font-size:13px; color:#3b82f6"></i>
                        </div>
                        <h3 style="font-size:15px; font-weight:700; color:var(--text-primary)">Thêm nhà cung cấp</h3>
                    </div>
                    <button type="button" @click="quickSupplier.open = false"
                            style="width:28px; height:28px; border-radius:6px; border:none; background:var(--surface-bg); color:var(--text-muted); cursor:pointer; display:flex; align-items:center; justify-content:center">
                        <i class="bi bi-x-lg" style="font-size:12px"></i>
                    </button>
                </div>
                <div style="display:flex; flex-direction:column; gap:12px">
                    <div>
                        <label class="create-label">Tên nhà cung cấp <span style="color:#ef4444">*</span></label>
                        <input type="text" x-model="quickSupplier.name" class="create-sidebar-input"
                               placeholder="Nhập tên nhà cung cấp..."
                               @keydown.enter.prevent="saveQuickSupplier()">
                        <p x-show="quickSupplier.errors.name" class="text-xs mt-1" style="color:#ef4444"
                           x-text="(quickSupplier.errors.name||[])[0]"></p>
                    </div>
                    <div>
                        <label class="create-label">Mã NCC <span style="font-weight:400; color:var(--text-muted)">(tự tạo nếu trống)</span></label>
                        <input type="text" x-model="quickSupplier.code" class="create-sidebar-input"
                               placeholder="VD: NCC-001"
                               @keydown.enter.prevent="saveQuickSupplier()">
                        <p x-show="quickSupplier.errors.code" class="text-xs mt-1" style="color:#ef4444"
                           x-text="(quickSupplier.errors.code||[])[0]"></p>
                    </div>
                    <div style="display:flex; gap:12px">
                        <div style="flex:1">
                            <label class="create-label">Điện thoại</label>
                            <input type="text" x-model="quickSupplier.phone" class="create-sidebar-input"
                                   placeholder="0912 345 678"
                                   @keydown.enter.prevent="saveQuickSupplier()">
                        </div>
                        <div style="flex:1">
                            <label class="create-label">Email</label>
                            <input type="email" x-model="quickSupplier.email" class="create-sidebar-input"
                                   placeholder="ncc@email.com"
                                   @keydown.enter.prevent="saveQuickSupplier()">
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px">
                    <button type="button" @click="quickSupplier.open = false" class="btn-ghost">Hủy</button>
                    <button type="button" @click="saveQuickSupplier()" :disabled="quickSupplier.saving" class="btn-primary">
                        <template x-if="quickSupplier.saving"><i class="bi bi-hourglass-split" style="font-size:11px"></i></template>
                        <template x-if="!quickSupplier.saving"><i class="bi bi-plus-lg" style="font-size:11px"></i></template>
                        <span x-text="quickSupplier.saving ? 'Đang lưu...' : 'Thêm nhà cung cấp'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Stock error dialog (xuất kho) --}}
        <div x-show="showStockDialog"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="absolute inset-0 bg-black/40" @click="showStockDialog = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center"
                         style="background:rgba(239,68,68,.12)">
                        <i class="bi bi-exclamation-triangle" style="font-size:16px; color:#dc2626"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-sm" style="color:var(--text-primary); margin-bottom:4px">Không đủ tồn kho</p>
                        <p class="text-sm" style="color:var(--text-secondary)" x-text="stockError"></p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" @click="showStockDialog = false"
                            style="font-size:13.5px; font-weight:600; padding:7px 18px; border-radius:8px; background:#dc2626; color:#fff; border:none; cursor:pointer">
                        Đã hiểu
                    </button>
                </div>
            </div>
        </div>
    </form>

    <style>
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--surface-card);
            border: 1px solid var(--surface-border);
            color: var(--text-muted);
            text-decoration: none;
            transition: background .15s;
            flex-shrink: 0;
        }

        .btn-icon:hover {
            background: var(--surface-bg);
        }

        .type-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .type-in {
            background: rgba(22, 163, 74, .10);
            color: #16a34a;
        }

        .type-out {
            background: rgba(249, 115, 22, .10);
            color: #ea580c;
        }

        .btn-ghost {
            font-size: 13.5px;
            padding: 7px 14px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }

        .btn-ghost:hover {
            background: var(--surface-bg);
        }

        .btn-outline {
            font-size: 13.5px;
            padding: 7px 14px;
            border-radius: 8px;
            color: var(--text-primary);
            background: var(--surface-card);
            border: 1px solid var(--surface-border);
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-outline:hover {
            background: var(--surface-bg);
        }

        .btn-primary {
            font-size: 13.5px;
            font-weight: 600;
            padding: 7px 16px;
            border-radius: 8px;
            color: #fff;
            background: #16a34a;
            border: none;
            cursor: pointer;
            transition: background .15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 3px rgba(22, 163, 74, .3);
            white-space: nowrap;
        }

        .btn-primary:hover {
            background: #15803d;
        }

        /* ── Action bar ── */
        .create-action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .create-action-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .create-action-subtitle {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .create-action-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        /* Short labels hidden on desktop, shown on mobile */
        .btn-label-short { display: none; }
        @media (max-width: 640px) {
            .create-action-subtitle { display: none; }
            .btn-label-full   { display: none; }
            .btn-label-short  { display: inline; }
            .btn-ghost        { display: none; }
            .create-action-right { gap: 6px; }
        }

        .btn-add-row {
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 7px;
            background: rgba(22, 163, 74, .08);
            color: #16a34a;
            border: 1px solid rgba(22, 163, 74, .2);
            cursor: pointer;
            transition: background .15s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-add-row:hover {
            background: rgba(22, 163, 74, .15);
        }

        .create-card {
            background: var(--surface-card);
            border: 1px solid var(--surface-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .create-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            border-bottom: 1px solid var(--surface-border);
        }

        .create-accent-bar {
            width: 3px;
            height: 16px;
            border-radius: 2px;
            background: #16a34a;
            flex-shrink: 0;
        }

        .create-row {
            transition: background .1s;
        }

        .create-row:hover {
            background: var(--surface-bg);
        }

        .create-row-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            background: var(--surface-bg);
            border: 1px solid var(--surface-border);
            color: var(--text-muted);
            transition: all .15s;
        }

        .create-row:hover .create-row-num {
            background: rgba(22, 163, 74, .08);
            border-color: rgba(22, 163, 74, .3);
            color: #16a34a;
        }

        .create-table-input,
        .create-table-select {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 1.5px solid var(--surface-border);
            color: var(--text-primary);
            font-size: 13.5px;
            padding: 4px 2px 5px;
            outline: none;
            transition: border-color .15s;
            border-radius: 0;
            cursor: pointer;
        }

        button.create-table-select {
            min-height: 29px;
        }

        .create-table-input:focus,
        .create-table-select:focus {
            border-bottom-color: #16a34a;
        }

        .create-table-input::placeholder {
            color: var(--text-muted);
        }

        .create-table-select:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .dark .create-table-input,
        .dark .create-table-select {
            background: transparent !important;
            border-color: var(--surface-border) !important;
            color: var(--text-primary) !important;
        }

        .create-delete-btn {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            opacity: 0;
            transition: opacity .15s, background .15s, color .15s;
            margin: 0 auto;
        }

        .create-row:hover .create-delete-btn {
            opacity: 1;
        }

        .create-delete-btn:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .dark .create-delete-btn:hover {
            background: rgba(220, 38, 38, .2);
        }

        .create-label {
            display: block;
            font-size: 11.5px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 6px;
            letter-spacing: .01em;
        }

        .create-sidebar-input {
            width: 100%;
            padding: 8px 10px;
            font-size: 13.5px;
            border-radius: 8px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            background: var(--surface-bg);
            border: 1.5px solid var(--surface-border);
            color: var(--text-primary);
        }

        .create-sidebar-input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .10);
        }

        .dark .create-sidebar-input {
            background: rgba(0, 0, 0, .2) !important;
            border-color: var(--surface-border) !important;
            color: var(--text-primary) !important;
        }

        .quick-add-btn {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1.5px solid rgba(22, 163, 74, .35);
            background: rgba(22, 163, 74, .08);
            color: #16a34a;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, border-color .15s;
        }

        .quick-add-btn:hover {
            background: rgba(22, 163, 74, .18);
            border-color: rgba(22, 163, 74, .6);
        }

    </style>

@endsection
