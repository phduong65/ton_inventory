@extends('layouts.admin')

@section('title', 'Sửa ' . $transaction->code)
@section('page-title', 'Sửa ' . $transaction->code)
@section('breadcrumb', 'Phiếu NK/XK / Sửa')

@php
    $type = $transaction->type;
    $requireApproval = \App\Models\Setting::get('require_approval', true);

    // Build initial rows from existing details
    $initialRows = $transaction->details->map(function($d, $i) {
        return [
            'id'                => $i + 1,
            'product_id'        => $d->product_id,
            'unit_id'           => $d->unit_id,
            'conversion_factor' => 1,
            'baseUnitName'      => '',
            'availableUnits'    => [],
            'availableStock'    => 0,
            'qty'               => (float)$d->qty,
            'price'             => (float)$d->price,
            'discount'          => (float)$d->discount,
            'vat'               => (float)$d->vat,
            'discountAmt'       => 0,
            'vatAmt'            => 0,
            'amount'            => (float)$d->amount,
        ];
    })->values()->toArray();
@endphp

@section('content')
<script>
    window.__txFormData  = @json($productsUnitData);
    window.__txInitRows  = @json($initialRows);
    window.__txNextId    = {{ $transaction->details->count() + 1 }};

    window.createForm = function(productsData) {
        return {
            productsData,
            type: '{{ $type }}',
            showStockDialog: false,
            stockError: '',
            submitting: false,
            rows: window.__txInitRows.map(r => Object.assign({}, r)),
            nextId: window.__txNextId,
            palette: { open: false, search: '', results: [], targetRowId: null, focusIdx: -1 },

            init() {
                this.rows.forEach(row => { if (row.product_id) this._initRow(row); });
            },
            _initRow(row) {
                const p = this.productsData[row.product_id];
                if (!p) return;
                const savedUnitId = row.unit_id;
                const units = [{ unitId: p.baseUnitId, unitName: p.baseUnitName, factor: 1 }];
                (p.conversions || []).forEach(c => units.push(c));
                row.baseUnitName   = p.baseUnitName;
                row.availableStock = p.stock ?? 0;
                row.availableUnits = units;
                const conv = units.find(u => u.unitId == savedUnitId);
                row.conversion_factor = conv ? conv.factor : 1;
                this.calcRow(row);
            },

            get total()         { return this.rows.reduce((s, r) => s + (r.amount || 0), 0); },
            get totalDiscount() { return this.rows.reduce((s, r) => s + (r.discountAmt || 0), 0); },
            get totalVat()      { return this.rows.reduce((s, r) => s + (r.vatAmt || 0), 0); },

            addRow() {
                this.rows.push({ id: this.nextId++, product_id: '', unit_id: '', conversion_factor: 1, baseUnitName: '', availableUnits: [], availableStock: 0, qty: 1, price: 0, discount: 0, vat: 0, discountAmt: 0, vatAmt: 0, amount: 0 });
            },
            removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },

            onProductChange(row) {
                const p = this.productsData[row.product_id];
                if (!p) { row.availableUnits = []; row.unit_id = ''; row.conversion_factor = 1; row.baseUnitName = ''; row.availableStock = 0; return; }
                const units = [{ unitId: p.baseUnitId, unitName: p.baseUnitName, factor: 1 }];
                (p.conversions || []).forEach(c => units.push(c));
                row.baseUnitName   = p.baseUnitName;
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
                const baseAmt   = qty * price;
                row.discountAmt = baseAmt * (ck / 100);
                row.vatAmt      = (baseAmt - row.discountAmt) * (vat / 100);
                row.amount      = baseAmt - row.discountAmt + row.vatAmt;
            },
            validateQty(row, el) {
                if (this.type !== 'OUT' || !row.product_id) return;
                const baseQty = (parseFloat(row.qty) || 0) * (parseFloat(row.conversion_factor) || 1);
                if (baseQty > row.availableStock) {
                    const unit = row.baseUnitName || '';
                    this.stockError = `Số lượng xuất (${this.formatQty(baseQty)} ${unit}) vượt quá tồn kho hiện tại (${this.formatQty(row.availableStock)} ${unit}).`;
                    this.showStockDialog = true;
                    this.$nextTick(() => el.focus());
                }
            },
            handleSubmit(event) {
                if (this.type === 'OUT') {
                    for (let i = 0; i < this.rows.length; i++) {
                        const row = this.rows[i];
                        if (!row.product_id) continue;
                        const baseQty = (parseFloat(row.qty) || 0) * (parseFloat(row.conversion_factor) || 1);
                        if (baseQty > row.availableStock) {
                            const unit = row.baseUnitName || '';
                            this.stockError = `Dòng ${i + 1}: Số lượng xuất (${this.formatQty(baseQty)} ${unit}) vượt quá tồn kho (${this.formatQty(row.availableStock)} ${unit}).`;
                            this.showStockDialog = true;
                            return;
                        }
                    }
                }
                if (this.submitting) return;
                this.submitting = true;
                this.$el.submit();
            },
            formatQty(n) { return new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 3 }).format(n); },
            formatNum(n) { return new Intl.NumberFormat('vi-VN').format(Math.round(n)) + 'đ'; },

            _allProducts() { return Object.entries(this.productsData).map(([id, p]) => ({ id: parseInt(id), ...p })); },
            openPalette(row) {
                this.palette.targetRowId = row.id; this.palette.search = ''; this.palette.focusIdx = -1;
                this.palette.results = this._allProducts(); this.palette.open = true;
                this.$nextTick(() => { document.getElementById('cp-search')?.focus(); document.getElementById('cp-list')?.scrollTo(0, 0); });
            },
            closePalette() { this.palette.open = false; this.palette.targetRowId = null; },
            filterPalette() {
                const q = this.palette.search.toLowerCase().trim();
                const all = this._allProducts();
                this.palette.results = q ? all.filter(p => p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q) || (p.category||'').toLowerCase().includes(q)) : all;
                this.palette.focusIdx = this.palette.results.length ? 0 : -1;
            },
            selectFromPalette(product) {
                const row = this.rows.find(r => r.id === this.palette.targetRowId);
                if (row) { row.product_id = product.id; this.onProductChange(row); }
                this.closePalette();
            },
            paletteMoveDown() { if (this.palette.focusIdx < this.palette.results.length - 1) { this.palette.focusIdx++; this.$nextTick(() => document.querySelector('.cp-focused')?.scrollIntoView({ block:'nearest' })); } },
            paletteMoveUp()   { if (this.palette.focusIdx > 0) { this.palette.focusIdx--; this.$nextTick(() => document.querySelector('.cp-focused')?.scrollIntoView({ block:'nearest' })); } },
            paletteConfirm()  { const item = this.palette.results[this.palette.focusIdx] ?? (this.palette.results.length === 1 ? this.palette.results[0] : null); if (item) this.selectFromPalette(item); },
        };
    };
</script>

<form action="{{ route('transactions.update', $transaction) }}" method="POST"
      x-data="createForm(window.__txFormData)"
      @submit.prevent="handleSubmit($event)">
    @csrf @method('PUT')

    {{-- Action bar --}}
    <div class="create-action-bar">
        <div class="create-action-left">
            <a href="{{ route('transactions.show', $transaction) }}" class="btn-icon" title="Quay lại">
                <i class="bi bi-arrow-left" style="font-size:13px"></i>
            </a>
            <span class="type-badge {{ $type === 'IN' ? 'type-in' : 'type-out' }}">
                {{ $type === 'IN' ? 'Nhập kho' : 'Xuất kho' }}
            </span>
            <span class="create-action-subtitle">/ {{ $transaction->code }}</span>
        </div>
        <div class="create-action-right">
            <a href="{{ route('transactions.show', $transaction) }}" class="btn-ghost">Hủy</a>
            <button type="submit" :disabled="submitting" class="btn-primary">
                <template x-if="submitting"><i class="bi bi-hourglass-split" style="font-size:11px"></i></template>
                <template x-if="!submitting"><i class="bi bi-floppy" style="font-size:11px"></i></template>
                <span>Lưu thay đổi</span>
            </button>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px">

        {{-- Info panel --}}
        <div class="create-card">
            <div class="create-card-header">
                <div class="create-accent-bar"></div>
                <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">Thông tin phiếu</span>
            </div>
            <div style="padding:16px; display:flex; flex-direction:column; gap:12px">
                <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap">
                    <div style="flex:1; min-width:160px">
                        <label class="create-label">
                            Ngày {{ $type === 'IN' ? 'nhập' : 'xuất' }} <span style="color:#ef4444">*</span>
                        </label>
                        <input type="date" name="date" value="{{ old('date', $transaction->date?->format('Y-m-d')) }}" required
                            class="create-sidebar-input">
                    </div>
                    @if($type === 'IN')
                    <div style="flex:2; min-width:200px">
                        <label class="create-label">Nhà cung cấp <span style="color:#ef4444">*</span></label>
                        <select name="supplier_id" required class="create-sidebar-input">
                            <option value="">Chọn nhà cung cấp...</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id', $transaction->supplier_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div style="flex:2; min-width:200px">
                        <label class="create-label">Điểm nhận <span style="color:#ef4444">*</span></label>
                        <select name="destination_id" required class="create-sidebar-input">
                            <option value="">Chọn kho nhận...</option>
                            @foreach($destinations as $d)
                            <option value="{{ $d->id }}" {{ old('destination_id', $transaction->destination_id) == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div>
                    <label class="create-label">Ghi chú</label>
                    <input type="text" name="note" value="{{ old('note', $transaction->note) }}"
                        placeholder="Ghi chú về lô hàng..." class="create-sidebar-input">
                </div>
            </div>
        </div>

        {{-- Product table --}}
        <div class="create-card">
            <div class="create-card-header">
                <div class="create-accent-bar"></div>
                <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">
                    Chi tiết {{ $type === 'IN' ? 'hàng nhập' : 'hàng xuất' }}
                </span>
                <span style="font-size:11px; padding:2px 8px; border-radius:99px; background:var(--surface-bg); color:var(--text-muted)">
                    <span x-text="rows.length"></span> dòng
                </span>
                <button type="button" @click="addRow()" class="btn-add-row" style="margin-left:auto">
                    <i class="bi bi-plus-lg" style="font-size:10px"></i> Thêm dòng
                </button>
            </div>

            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch">
                <table style="width:100%; min-width:{{ $type === 'IN' ? '820px' : '560px' }}; font-size:13.5px; border-collapse:collapse">
                    <thead>
                        <tr style="background:var(--surface-bg); border-bottom:1px solid var(--surface-border)">
                            <th class="px-4 py-2.5 text-left" style="width:36px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">#</th>
                            <th class="px-3 py-2.5 text-left" style="min-width:180px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">SẢN PHẨM</th>
                            <th class="px-3 py-2.5 text-left" style="width:90px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">ĐƠN VỊ</th>
                            <th class="px-3 py-2.5 text-right" style="width:90px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">SỐ LƯỢNG</th>
                            <th class="px-3 py-2.5 text-right" style="width:96px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">QUY ĐỔI</th>
                            @if($type === 'IN')
                            <th class="px-3 py-2.5 text-right" style="width:100px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">ĐƠN GIÁ</th>
                            <th class="px-3 py-2.5 text-right" style="width:58px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">CK%</th>
                            <th class="px-3 py-2.5 text-right" style="width:58px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">VAT%</th>
                            <th class="px-3 py-2.5 text-right" style="width:110px; color:var(--text-muted); font-size:10px; font-weight:500; letter-spacing:.04em">THÀNH TIỀN</th>
                            @endif
                            <th class="px-3 py-2.5" style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in rows" :key="row.id">
                            <tr class="group/row create-row" style="border-top:1px solid var(--surface-border)">
                                <td class="px-4 py-3"><span class="create-row-num" x-text="i + 1"></span></td>

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

                                <td class="px-3 py-2.5">
                                    <select :name="`details[${i}][unit_id]`" x-model="row.unit_id"
                                        @change="onUnitChange(row)" :disabled="!row.product_id" required
                                        class="create-table-select">
                                        <option value="">ĐVT...</option>
                                        <template x-for="u in row.availableUnits" :key="u.unitId">
                                            <option :value="u.unitId" x-text="u.unitName"></option>
                                        </template>
                                    </select>
                                    <input type="hidden" :name="`details[${i}][conversion_factor]`" :value="row.conversion_factor">
                                </td>

                                <td class="px-3 py-2.5">
                                    <input type="number" :name="`details[${i}][qty]`" x-model="row.qty"
                                        @input="calcRow(row)" @change="validateQty(row, $el)"
                                        min="0.001" step="any" required class="create-table-input text-right">
                                </td>

                                <td class="px-3 py-2.5 text-right tabular-nums" style="font-size:12px; color:var(--text-muted)">
                                    <span x-show="row.conversion_factor > 1"
                                        x-text="formatQty(row.qty * row.conversion_factor) + ' ' + row.baseUnitName"
                                        style="white-space:nowrap"></span>
                                    <span x-show="row.conversion_factor <= 1" style="color:var(--surface-border)">—</span>
                                </td>

                                @if($type === 'IN')
                                <td class="px-3 py-2.5">
                                    <input type="number" :name="`details[${i}][price]`" x-model="row.price"
                                        @input="calcRow(row)" min="0" step="1000" class="create-table-input text-right">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="number" :name="`details[${i}][discount]`" x-model="row.discount"
                                        @input="calcRow(row)" min="0" max="100" step="any" placeholder="0"
                                        class="create-table-input text-right">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="number" :name="`details[${i}][vat]`" x-model="row.vat"
                                        @input="calcRow(row)" min="0" max="100" step="1" placeholder="0"
                                        class="create-table-input text-right">
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold tabular-nums"
                                    style="color:var(--text-primary); font-size:13.5px"
                                    x-text="row.amount ? formatNum(row.amount) : '—'">
                                </td>
                                @endif

                                <td class="px-3 py-2.5 text-center">
                                    <button type="button" @click="removeRow(i)" class="create-delete-btn">
                                        <i class="bi bi-x" style="font-size:14px; line-height:1"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    @if($type === 'IN')
                    <tfoot>
                        <tr style="border-top:1px dashed var(--surface-border)">
                            <td colspan="8" class="px-5 py-2 text-right text-xs font-medium" style="color:var(--text-muted)">Tổng chiết khấu</td>
                            <td class="px-5 py-2 text-right text-sm tabular-nums font-semibold" style="color:#f97316">
                                {{-- <span x-show="totalDiscount > 0" x-text="'- ' + formatNum(totalDiscount)"></span> --}}
                                <span x-show="totalDiscount <= 0" style="color:var(--surface-border)">—</span>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="8" class="px-5 py-2 text-right text-xs font-medium" style="color:var(--text-muted)">Tổng VAT</td>
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
                                    <span class="text-xs font-semibold uppercase tracking-widest" style="color:var(--text-muted)">Tổng cộng</span>
                                    <span class="text-2xl font-bold tabular-nums" style="color:#16a34a; letter-spacing:-.02em" x-text="formatNum(total)"></span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Command Palette --}}
    <div x-show="palette.open" @keydown.escape.window="closePalette()"
         @keydown.arrow-down.window.prevent="paletteMoveDown()"
         @keydown.arrow-up.window.prevent="paletteMoveUp()"
         @keydown.enter.window.prevent="paletteConfirm()"
         class="cp-overlay" style="display:none">
        <div class="cp-backdrop" @click="closePalette()"></div>
        <div class="cp-panel">
            <div class="cp-searchbar">
                <i class="bi bi-search"></i>
                <input id="cp-search" class="cp-search-input" type="text" autocomplete="off"
                       placeholder="Tìm theo tên, SKU, danh mục..."
                       x-model="palette.search" @input="filterPalette()">
                <span class="cp-kbd">ESC</span>
            </div>
            <div id="cp-list" class="cp-list">
                <template x-if="palette.results.length === 0">
                    <div class="cp-empty"><i class="bi bi-search"></i> Không tìm thấy sản phẩm</div>
                </template>
                <template x-for="(p, idx) in palette.results" :key="p.id">
                    <button type="button" class="cp-item"
                            :class="palette.focusIdx === idx ? 'cp-focused' : ''"
                            @click="selectFromPalette(p)" @mouseenter="palette.focusIdx = idx">
                        <div class="cp-item-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="cp-item-body">
                            <div class="cp-item-top">
                                <span class="cp-item-name" x-text="p.name"></span>
                                <span class="cp-item-sku" x-show="p.sku" x-text="p.sku"></span>
                            </div>
                            <div class="cp-item-cat" x-show="p.category" x-text="p.category"></div>
                        </div>
                        <div class="cp-item-stock">
                            <div class="cp-item-qty" :style="p.stock <= 0 ? 'color:#ef4444' : 'color:#16a34a'"
                                 x-text="new Intl.NumberFormat('vi-VN').format(p.stock)"></div>
                            <div class="cp-item-unit" x-text="p.baseUnitName"></div>
                        </div>
                    </button>
                </template>
            </div>
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

    {{-- Stock error dialog --}}
    <div x-show="showStockDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/40" @click="showStockDialog = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-5">
            <div class="flex items-start gap-3 mb-4">
                <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center" style="background:rgba(239,68,68,.12)">
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
@endsection
