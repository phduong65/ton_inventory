@extends('layouts.admin')

@section('title', 'Báo cáo nhập kho')
@section('page-title', 'Báo cáo nhập kho')
@section('breadcrumb', 'Báo cáo / Nhập kho')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end" style="border-bottom:1px solid var(--surface-border)">
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Từ ngày</label>
            <x-date-picker name="date_from" :value="$from" class="w-36" placeholder="Từ ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đến ngày</label>
            <x-date-picker name="date_to" :value="$to" class="w-36" placeholder="Đến ngày" />
        </div>
        @php
        $supplierItems = array_merge(
            [['v' => '', 'l' => 'Tất cả NCC']],
            $suppliers->map(fn($s) => ['v' => $s->id, 'l' => $s->name])->toArray()
        );
        $productItems = array_merge(
            [['v' => '', 'l' => 'Tất cả sản phẩm']],
            $products->map(fn($p) => ['v' => $p->id, 'l' => $p->name, 's' => $p->sku ?? ''])->toArray()
        );
        @endphp

        <div class="flex flex-col gap-1"
             x-data="selectPalette({ value: '{{ request('supplier_id') ?? '' }}', items: {{ Js::from($supplierItems) }} })"
             @keydown.escape.window="if(open){ close(); $event.stopPropagation(); }"
             @keydown.arrow-down.window.prevent="if(open) moveDown()"
             @keydown.arrow-up.window.prevent="if(open) moveUp()"
             @keydown.enter.window.prevent="if(open) confirm()">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Nhà cung cấp</label>
            <input type="hidden" name="supplier_id" :value="currentValue">
            <button type="button" class="sp-trigger w-48" @click="openPalette()">
                <span x-text="currentLabel || 'Tất cả NCC'"></span>
                <i class="bi bi-search"></i>
            </button>
            @include('partials.select-palette', ['placeholder' => 'Tìm nhà cung cấp...', 'countLabel' => 'NCC'])
        </div>

        <div class="flex flex-col gap-1"
             x-data="selectPalette({ value: '{{ request('product_id') ?? '' }}', items: {{ Js::from($productItems) }} })"
             @keydown.escape.window="if(open){ close(); $event.stopPropagation(); }"
             @keydown.arrow-down.window.prevent="if(open) moveDown()"
             @keydown.arrow-up.window.prevent="if(open) moveUp()"
             @keydown.enter.window.prevent="if(open) confirm()">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</label>
            <input type="hidden" name="product_id" :value="currentValue">
            <button type="button" class="sp-trigger w-48" @click="openPalette()">
                <span x-text="currentLabel || 'Tất cả sản phẩm'"></span>
                <i class="bi bi-search"></i>
            </button>
            @include('partials.select-palette', ['placeholder' => 'Tìm theo tên, SKU...', 'countLabel' => 'sản phẩm'])
        </div>

        <div class="flex gap-2 self-end">
            <button type="submit" class="h-9 px-4 text-sm font-medium text-white rounded-xl transition-colors"
                    style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                <i class="ph ph-magnifying-glass mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('reports.receipts.export', request()->query()) }}"
               class="h-9 px-4 inline-flex items-center gap-1.5 text-sm font-medium text-white rounded-xl transition-colors"
               style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <i class="ph ph-file-xls text-base"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Summary bar --}}
    @if($rows->count())
    <div class="px-5 py-2.5 flex gap-6 text-sm" style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
        <span style="color:var(--text-muted)">Tổng dòng: <strong style="color:var(--text-primary)">{{ number_format($rows->count()) }}</strong></span>
        <span style="color:var(--text-muted)">Tổng SL: <strong style="color:var(--text-primary)">{{ number_format($rows->sum('qty'), 0, ',', '.') }}</strong></span>
        <span style="color:var(--text-muted)">Tổng tiền: <strong style="color:#16a34a">{{ number_format($rows->sum('amount'), 0, ',', '.') }}đ</strong></span>
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ngày</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Số phiếu</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Nhà cung cấp</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SL</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đơn giá</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">CK%</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">VAT%</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-2.5 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ $row->transaction?->date?->format('d/m/Y') }}</td>
                    <td class="px-5 py-2.5">
                        <a href="{{ route('transactions.show', $row->transaction_id) }}"
                           class="font-mono text-xs font-semibold hover:underline" style="color:var(--sidebar-accent)">
                            {{ $row->transaction?->code }}
                        </a>
                    </td>
                    <td class="px-5 py-2.5 text-xs" style="color:var(--text-secondary)">{{ $row->transaction?->supplier?->name ?? '—' }}</td>
                    <td class="px-5 py-2.5">
                        <div class="text-sm font-medium" style="color:var(--text-primary)">{{ $row->product?->name }}</div>
                        <div class="text-[10px] font-mono" style="color:var(--text-muted)">{{ $row->product?->sku }}</div>
                    </td>
                    <td class="px-5 py-2.5 text-xs" style="color:var(--text-muted)">{{ $row->product?->unit?->name ?? '—' }}</td>
                    <td class="px-5 py-2.5 text-right font-medium tabular-nums text-sm" style="color:var(--text-primary)">{{ number_format($row->qty, 0, ',', '.') }}</td>
                    <td class="px-5 py-2.5 text-right text-xs tabular-nums" style="color:var(--text-secondary)">{{ number_format($row->price, 0, ',', '.') }}</td>
                    <td class="px-5 py-2.5 text-right text-xs tabular-nums" style="color:var(--text-muted)">{{ $row->discount > 0 ? $row->discount.'%' : '—' }}</td>
                    <td class="px-5 py-2.5 text-right text-xs tabular-nums" style="color:var(--text-muted)">{{ $row->vat > 0 ? $row->vat.'%' : '—' }}</td>
                    <td class="px-5 py-2.5 text-right font-medium tabular-nums text-sm" style="color:var(--text-primary)">{{ number_format($row->amount, 0, ',', '.') }}đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-5 py-16 text-center">
                        <i class="ph ph-file-text text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Không có dữ liệu trong kỳ này</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot>
                <tr style="background:var(--surface-bg);border-top:2px solid var(--surface-border)">
                    <td colspan="5" class="px-5 py-3 text-sm font-semibold" style="color:var(--text-secondary)">Tổng cộng</td>
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($rows->sum('qty'), 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                    <td class="px-5 py-3 text-right text-sm font-bold tabular-nums" style="color:#16a34a">{{ number_format($rows->sum('amount'), 0, ',', '.') }}đ</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
