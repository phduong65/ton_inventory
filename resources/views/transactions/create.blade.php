@extends('layouts.admin')

@section('title', $type === 'IN' ? 'Tạo phiếu nhập' : 'Tạo phiếu xuất')
@section('page-title', $type === 'IN' ? 'Tạo phiếu nhập kho' : 'Tạo phiếu xuất kho')
@section('breadcrumb', 'Phiếu NK/XK / ' . ($type === 'IN' ? 'Nhập kho' : 'Xuất kho'))

@section('content')
<form action="{{ route('transactions.store') }}" method="POST"
      enctype="multipart/form-data"
      x-data="createForm()">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">

    {{-- Action bar --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px">
        <div style="display:flex; align-items:center; gap:10px">
            <a href="{{ route('transactions.index') }}" class="btn-icon" title="Quay lại">
                <i class="bi bi-arrow-left" style="font-size:13px"></i>
            </a>
            <span class="type-badge {{ $type === 'IN' ? 'type-in' : 'type-out' }}">
                {{ $type === 'IN' ? 'Nhập kho' : 'Xuất kho' }}
            </span>
            <span style="font-size:14px; font-weight:500; color:var(--text-secondary)">/ Phiếu mới</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px">
            <a href="{{ route('transactions.index') }}" class="btn-ghost">Hủy</a>
            <button type="submit" name="save" class="btn-outline">
                <i class="bi bi-floppy" style="font-size:12px"></i> Lưu nháp
            </button>
            <button type="submit" name="submit" class="btn-primary">
                <i class="bi bi-send" style="font-size:11px"></i> Submit chờ duyệt
            </button>
        </div>
    </div>

    {{-- Single column layout --}}
    <div style="display:flex; flex-direction:column; gap:16px">

        {{-- Info panel (top) --}}
        <div class="create-card">
            <div class="create-card-header">
                <div class="create-accent-bar"></div>
                <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">Thông tin phiếu</span>
            </div>
            <div style="padding:16px; display:flex; gap:16px; align-items:flex-start">
                <div style="flex:1; min-width:0">
                    <label class="create-label">
                        Ngày {{ $type === 'IN' ? 'nhập' : 'xuất' }}
                        <span style="color:#ef4444">*</span>
                    </label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                           class="create-sidebar-input">
                </div>
                @if($type === 'IN')
                <div style="flex:1; min-width:0">
                    <label class="create-label">Nhà cung cấp <span style="color:#ef4444">*</span></label>
                    <select name="supplier_id" required class="create-sidebar-input">
                        <option value="">Chọn nhà cung cấp...</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div style="flex:1; min-width:0">
                    <label class="create-label">Điểm nhận <span style="color:#ef4444">*</span></label>
                    <select name="destination_id" required class="create-sidebar-input">
                        <option value="">Chọn kho nhận...</option>
                        @foreach($destinations as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div style="flex:2; min-width:0">
                    <label class="create-label">Ghi chú</label>
                    <input type="text" name="note" placeholder="Ghi chú về lô hàng..."
                           class="create-sidebar-input">
                </div>
            </div>
        </div>

        {{-- Product table --}}
        <div>
            <div class="create-card">
                {{-- Table toolbar --}}
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

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full" style="font-size:13.5px">
                        <thead>
                            <tr style="background:var(--surface-bg); border-bottom:1px solid var(--surface-border)">
                                <th class="px-5 py-2.5 text-left w-10" style="color:var(--text-muted); font-size:11px; font-weight:500; letter-spacing:.04em">#</th>
                                <th class="px-3 py-2.5 text-left" style="color:var(--text-muted); font-size:11px; font-weight:500; letter-spacing:.04em">SẢN PHẨM</th>
                                <th class="px-3 py-2.5 text-right w-28" style="color:var(--text-muted); font-size:11px; font-weight:500; letter-spacing:.04em">SỐ LƯỢNG</th>
                                @if($type === 'IN')
                                <th class="px-3 py-2.5 text-right w-36" style="color:var(--text-muted); font-size:11px; font-weight:500; letter-spacing:.04em">ĐƠN GIÁ</th>
                                <th class="px-3 py-2.5 text-right w-20" style="color:var(--text-muted); font-size:11px; font-weight:500; letter-spacing:.04em">CK%</th>
                                <th class="px-3 py-2.5 text-right w-20" style="color:var(--text-muted); font-size:11px; font-weight:500; letter-spacing:.04em">VAT%</th>
                                <th class="px-3 py-2.5 text-right w-36" style="color:var(--text-muted); font-size:11px; font-weight:500; letter-spacing:.04em">THÀNH TIỀN</th>
                                @endif
                                <th class="px-3 py-2.5 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in rows" :key="row.id">
                                <tr class="group/row create-row"
                                    style="border-top:1px solid var(--surface-border)">

                                    {{-- Row number badge --}}
                                    <td class="px-5 py-3">
                                        <span class="create-row-num" x-text="i + 1"></span>
                                    </td>

                                    {{-- Product select --}}
                                    <td class="px-3 py-2.5">
                                        <select :name="`details[${i}][product_id]`"
                                                x-model="row.product_id" required
                                                class="create-table-select">
                                            <option value="">Chọn sản phẩm...</option>
                                            @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->unit }}){{ $type === 'OUT' ? ' · Tồn: '.number_format($p->inventory?->quantity ?? 0) : '' }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- Qty --}}
                                    <td class="px-3 py-2.5">
                                        <input type="number" :name="`details[${i}][qty]`"
                                               x-model="row.qty" @input="calcRow(row)"
                                               min="0.001" step="0.001" required
                                               class="create-table-input text-right">
                                    </td>

                                    @if($type === 'IN')
                                    {{-- Unit price --}}
                                    <td class="px-3 py-2.5">
                                        <input type="number" :name="`details[${i}][price]`"
                                               x-model="row.price" @input="calcRow(row)"
                                               min="0" step="1000"
                                               class="create-table-input text-right">
                                    </td>

                                    {{-- Discount --}}
                                    <td class="px-3 py-2.5">
                                        <input type="number" :name="`details[${i}][discount]`"
                                               x-model="row.discount" @input="calcRow(row)"
                                               min="0" max="100" step="0.5" placeholder="0"
                                               class="create-table-input text-right">
                                    </td>

                                    {{-- VAT --}}
                                    <td class="px-3 py-2.5">
                                        <input type="number" :name="`details[${i}][vat]`"
                                               x-model="row.vat" @input="calcRow(row)"
                                               min="0" max="100" step="1" placeholder="0"
                                               class="create-table-input text-right">
                                    </td>

                                    {{-- Amount --}}
                                    <td class="px-3 py-2.5 text-right font-semibold tabular-nums"
                                        style="color:var(--text-primary); font-size:13.5px"
                                        x-text="row.amount ? formatNum(row.amount) : '—'">
                                    </td>
                                    @endif

                                    {{-- Delete --}}
                                    <td class="px-3 py-2.5 text-center">
                                        <button type="button" @click="removeRow(i)"
                                                class="create-delete-btn">
                                            <i class="bi bi-x" style="font-size:14px; line-height:1"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>

                        @if($type === 'IN')
                        <tfoot>
                            <tr>
                                <td colspan="9">
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
                                            <span class="text-sm font-medium" style="color:var(--text-muted)">VND</span>
                                            <span class="text-2xl font-bold tabular-nums" style="color:#16a34a; letter-spacing:-.02em"
                                                  x-text="formatNumClean(total)"></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Upload panel (IN only) --}}
            @if($type === 'IN')
            <div class="create-card"
                 x-data="imageUpload()"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="handleDrop($event)">

                <div class="create-card-header">
                    <div class="create-accent-bar"></div>
                    <span style="font-size:13.5px; font-weight:600; color:var(--text-primary)">Ảnh đính kèm</span>
                    <span x-show="previews.length > 0"
                          style="font-size:11px; padding:2px 8px; border-radius:99px; background:var(--surface-bg); color:var(--text-muted)">
                        <span x-text="previews.length"></span>/10
                    </span>
                    <span style="margin-left:auto; font-size:11px; color:var(--text-muted)">≤ 5MB/ảnh</span>
                </div>

                {{-- Empty drop zone --}}
                <label x-show="previews.length === 0"
                       class="upload-dropzone"
                       :class="dragging ? 'dragging' : ''"
                       style="margin:12px; padding:20px 24px; flex-direction:row; gap:16px; justify-content:center">
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="sr-only" @change="handleFiles($event)">
                    <div class="upload-icon-ring">
                        <i class="bi bi-cloud-arrow-up" style="font-size:20px"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-center" style="color:var(--text-secondary)">
                            <span style="color:#16a34a; font-weight:600">Chọn ảnh</span> hoặc kéo vào đây
                        </p>
                        <p class="text-xs text-center mt-0.5" style="color:var(--text-muted)">JPG, PNG, WEBP · Tối đa 10 ảnh</p>
                    </div>
                </label>

                {{-- Grid --}}
                <div x-show="previews.length > 0" class="p-3">
                    <div class="upload-grid">
                        <template x-for="(img, i) in previews" :key="img.id">
                            <div class="upload-thumb">
                                <img :src="img.url" alt="" @click="openLightbox(i)" style="cursor:zoom-in">
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
                        <label x-show="previews.length < 10"
                               class="upload-add-cell"
                               :class="dragging ? 'dragging' : ''">
                            <input type="file" name="images[]" multiple accept="image/*"
                                   class="sr-only" @change="handleFiles($event)">
                            <i class="bi bi-plus" style="font-size:18px"></i>
                            <span>Thêm</span>
                        </label>
                    </div>
                </div>

                {{-- Lightbox --}}
                <div x-show="lb.open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex flex-col"
                     style="background:rgba(0,0,0,0.92); backdrop-filter:blur(8px)"
                     @click.self="lb.open = false"
                     @keydown.escape.window="lb.open = false"
                     @keydown.arrow-left.window="lb.open && lbPrev()"
                     @keydown.arrow-right.window="lb.open && lbNext()">

                    <div class="flex items-center justify-between px-6 py-4 flex-shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-white text-sm font-medium truncate" x-text="previews[lb.current]?.name"></span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-mono flex-shrink-0"
                                  style="background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.6)">
                                <span x-text="lb.current + 1"></span>/<span x-text="previews.length"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="text-xs" style="color:rgba(255,255,255,0.4)" x-text="previews[lb.current]?.size"></span>
                            <button @click="lb.open = false"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                    style="background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.8)"
                                    onmouseover="this.style.background='rgba(255,255,255,0.15)'"
                                    onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                                <i class="bi bi-x-lg" style="font-size:13px"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center justify-center px-16 pb-4 min-h-0 relative">
                        <button x-show="previews.length > 1" @click="lbPrev()"
                                class="absolute left-4 w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                                style="background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.8)"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <img :src="previews[lb.current]?.url"
                             :alt="previews[lb.current]?.name"
                             class="max-h-full max-w-full object-contain rounded-xl select-none"
                             style="box-shadow:0 32px 80px rgba(0,0,0,0.6)">

                        <button x-show="previews.length > 1" @click="lbNext()"
                                class="absolute right-4 w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                                style="background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.8)"
                                onmouseover="this.style.background='rgba(255,255,255,0.2)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>

                    <div x-show="previews.length > 1"
                         class="flex items-center justify-center gap-1.5 py-3 flex-shrink-0">
                        <template x-for="(_, i) in previews" :key="i">
                            <button @click="lb.current = i"
                                    :class="i === lb.current ? 'w-5 h-1.5' : 'w-1.5 h-1.5 opacity-35 hover:opacity-60'"
                                    class="rounded-full transition-all duration-200"
                                    style="background:#fff"></button>
                        </template>
                    </div>

                    <div x-show="previews.length > 1"
                         class="flex items-center justify-center gap-2 px-6 pb-5 flex-shrink-0 overflow-x-auto">
                        <template x-for="(img, i) in previews" :key="img.id">
                            <button type="button" @click="lb.current = i"
                                    class="w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 transition-all duration-150"
                                    :style="i === lb.current
                                        ? 'outline:2px solid #4ade80; outline-offset:2px; opacity:1'
                                        : 'opacity:0.4'">
                                <img :src="img.url" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            @endif

    </div>
</form>

<style>
/* ── Buttons ─────────────────────────────────────────── */
.btn-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-card);
    border: 1px solid var(--surface-border);
    color: var(--text-muted);
    text-decoration: none;
    transition: background 0.15s;
    flex-shrink: 0;
}
.btn-icon:hover { background: var(--surface-bg); }

.type-badge {
    font-size: 11px; font-weight: 700;
    padding: 3px 10px; border-radius: 6px;
    letter-spacing: .06em; text-transform: uppercase;
}
.type-in  { background: rgba(22,163,74,0.10); color: #16a34a; }
.type-out { background: rgba(249,115,22,0.10); color: #ea580c; }

.btn-ghost {
    font-size: 13.5px; padding: 7px 14px; border-radius: 8px;
    color: var(--text-secondary); text-decoration: none;
    background: transparent; border: none; cursor: pointer;
    transition: background 0.15s; white-space: nowrap;
}
.btn-ghost:hover { background: var(--surface-bg); }

.btn-outline {
    font-size: 13.5px; padding: 7px 14px; border-radius: 8px;
    color: var(--text-primary);
    background: var(--surface-card);
    border: 1px solid var(--surface-border);
    cursor: pointer; transition: background 0.15s; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-outline:hover { background: var(--surface-bg); }

.btn-primary {
    font-size: 13.5px; font-weight: 600; padding: 7px 16px; border-radius: 8px;
    color: #fff; background: #16a34a; border: none;
    cursor: pointer; transition: background 0.15s;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 1px 3px rgba(22,163,74,0.3); white-space: nowrap;
}
.btn-primary:hover { background: #15803d; }

.btn-add-row {
    font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 7px;
    background: rgba(22,163,74,0.08); color: #16a34a;
    border: 1px solid rgba(22,163,74,0.2);
    cursor: pointer; transition: background 0.15s;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-add-row:hover { background: rgba(22,163,74,0.15); }

/* ── Card component ──────────────────────────────────── */
.create-card {
    background: var(--surface-card);
    border: 1px solid var(--surface-border);
    border-radius: 12px;
    overflow: hidden;
}
.create-card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--surface-border);
}
.create-accent-bar {
    width: 3px; height: 16px; border-radius: 2px;
    background: #16a34a; flex-shrink: 0;
}

/* ── Table row ───────────────────────────────────────── */
.create-row { transition: background 0.1s; }
.create-row:hover { background: var(--surface-bg); }

/* Row number badge */
.create-row-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px; height: 22px;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    background: var(--surface-bg);
    border: 1px solid var(--surface-border);
    color: var(--text-muted);
    transition: all 0.15s;
}
.create-row:hover .create-row-num {
    background: rgba(22,163,74,0.08);
    border-color: rgba(22,163,74,0.3);
    color: #16a34a;
}

/* Table inputs — underline style, no box */
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
    transition: border-color 0.15s;
    border-radius: 0;
}
.create-table-input:focus,
.create-table-select:focus {
    border-bottom-color: #16a34a;
}
.create-table-input::placeholder { color: var(--text-muted); }
.dark .create-table-input,
.dark .create-table-select {
    background: transparent !important;
    border-color: var(--surface-border) !important;
    color: var(--text-primary) !important;
}

/* Delete button — reveals on row hover */
.create-delete-btn {
    width: 24px; height: 24px;
    border-radius: 6px;
    border: none;
    display: flex; align-items: center; justify-content: center;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.15s, background 0.15s, color 0.15s;
    margin: 0 auto;
}
.create-row:hover .create-delete-btn { opacity: 1; }
.create-delete-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}
.dark .create-delete-btn:hover { background: rgba(220,38,38,0.2); }

/* Sidebar inputs */
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
    transition: border-color 0.15s, box-shadow 0.15s;
    background: var(--surface-bg);
    border: 1.5px solid var(--surface-border);
    color: var(--text-primary);
}
.create-sidebar-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,0.10);
}
.dark .create-sidebar-input {
    background: rgba(0,0,0,0.2) !important;
    border-color: var(--surface-border) !important;
    color: var(--text-primary) !important;
}

/* Upload thumb hover elements */
.upload-thumb-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0);
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.upload-thumb:hover .upload-thumb-overlay { background: rgba(0,0,0,0.30); }

.upload-zoom-btn {
    width: 32px; height: 32px;
    border-radius: 50%; border: none;
    background: rgba(255,255,255,0.92);
    color: #111;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    opacity: 0; transform: scale(0.7);
    transition: opacity 0.15s, transform 0.15s;
}
.upload-thumb:hover .upload-zoom-btn { opacity: 1; transform: scale(1); }

.upload-delete-corner {
    position: absolute; top: 4px; right: 4px;
    width: 20px; height: 20px;
    border-radius: 50%; border: none;
    background: #ef4444; color: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; line-height: 1;
    opacity: 0; transform: scale(0.7);
    transition: opacity 0.15s, transform 0.15s;
}
.upload-thumb:hover .upload-delete-corner { opacity: 1; transform: scale(1); }
.upload-delete-corner:hover { background: #dc2626; }
</style>

<script>
function imageUpload() {
    return {
        dragging: false,
        previews: [],
        _uid: 0,
        lb: { open: false, current: 0 },

        openLightbox(i) { this.lb.current = i; this.lb.open = true; },
        lbPrev() { this.lb.current = (this.lb.current - 1 + this.previews.length) % this.previews.length; },
        lbNext() { this.lb.current = (this.lb.current + 1) % this.previews.length; },

        handleFiles(e) { this.addFiles([...e.target.files]); e.target.value = ''; },
        handleDrop(e) {
            this.dragging = false;
            this.addFiles([...e.dataTransfer.files].filter(f => f.type.startsWith('image/')));
        },
        addFiles(files) {
            const rem = 10 - this.previews.length;
            files.slice(0, rem).forEach(file => {
                if (file.size > 5 * 1024 * 1024) { alert(`${file.name} vượt quá 5MB`); return; }
                const kb = file.size / 1024;
                const size = kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : Math.round(kb) + ' KB';
                const reader = new FileReader();
                reader.onload = e => {
                    this.previews.push({ id: ++this._uid, url: e.target.result, name: file.name, size, file });
                    this.syncInput();
                };
                reader.readAsDataURL(file);
            });
        },
        removePreview(i) {
            if (this.lb.open && this.lb.current >= this.previews.length - 1)
                this.lb.current = Math.max(0, this.previews.length - 2);
            this.previews.splice(i, 1);
            if (this.previews.length === 0) this.lb.open = false;
            this.syncInput();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.previews.forEach(p => dt.items.add(p.file));
            this.$el.querySelectorAll('input[type="file"]').forEach(inp => { inp.files = dt.files; });
        }
    };
}

function createForm() {
    return {
        rows: [{ id: 1, product_id: '', qty: 1, price: 0, discount: 0, vat: 0, amount: 0 }],
        nextId: 2,
        get total() { return this.rows.reduce((s, r) => s + (r.amount || 0), 0); },
        addRow() {
            this.rows.push({ id: this.nextId++, product_id: '', qty: 1, price: 0, discount: 0, vat: 0, amount: 0 });
        },
        removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
        calcRow(row) {
            const qty = parseFloat(row.qty) || 0;
            const price = parseFloat(row.price) || 0;
            const ck = parseFloat(row.discount) || 0;
            const vat = parseFloat(row.vat) || 0;
            row.amount = qty * price * (1 - ck / 100) * (1 + vat / 100);
        },
        formatNum(n) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(n)) + 'đ';
        },
        formatNumClean(n) {
            if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(2).replace(/\.?0+$/, '') + ' tỷ';
            if (n >= 1_000_000) return (n / 1_000_000).toFixed(1).replace(/\.0$/, '') + ' triệu';
            return new Intl.NumberFormat('vi-VN').format(Math.round(n));
        }
    };
}
</script>
@endsection
