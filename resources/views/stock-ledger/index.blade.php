@extends('layouts.admin')

@section('title', 'Thẻ kho')
@section('page-title', 'Thẻ kho')
@section('breadcrumb', 'Kho / Thẻ kho')

@section('content')

<div class="flex justify-end mb-5">
    @can('export-stock-ledger')
    <a href="{{ route('stock-ledger.export', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
       style="background:#16a34a"
       onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
        <i class="ph ph-file-xls text-base"></i> Xuất Excel
    </a>
    @endcan
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3" style="border-bottom:1px solid var(--surface-border)">
        @php
        $productItems = array_merge(
            [['v' => '', 'l' => 'Tất cả sản phẩm']],
            $products->map(fn($p) => ['v' => $p->id, 'l' => $p->name, 's' => $p->sku ?? ''])->toArray()
        );
        @endphp
        <div x-data="selectPalette({ value: '{{ request('product_id') ?? '' }}', items: {{ Js::from($productItems) }} })"
             @keydown.escape.window="if(open){ close(); $event.stopPropagation(); }"
             @keydown.arrow-down.window.prevent="if(open) moveDown()"
             @keydown.arrow-up.window.prevent="if(open) moveUp()"
             @keydown.enter.window.prevent="if(open) confirm()">
            <input type="hidden" name="product_id" :value="currentValue">
            <button type="button" class="sp-trigger w-56" @click="openPalette()">
                <span x-text="currentLabel || 'Tất cả sản phẩm'"></span>
                <i class="bi bi-search"></i>
            </button>
            @include('partials.select-palette', ['placeholder' => 'Tìm theo tên, SKU...', 'countLabel' => 'sản phẩm'])
        </div>
        <select name="type" class="form-input h-9 text-sm w-36">
            <option value="">Tất cả loại</option>
            <option value="IN"         {{ request('type') === 'IN'         ? 'selected' : '' }}>Nhập</option>
            <option value="OUT"        {{ request('type') === 'OUT'        ? 'selected' : '' }}>Xuất</option>
            <option value="ADJUSTMENT" {{ request('type') === 'ADJUSTMENT' ? 'selected' : '' }}>Điều chỉnh</option>
        </select>
        <x-date-picker name="date_from" value="{{ request('date_from') }}" class="w-36" placeholder="Từ ngày" />
        <x-date-picker name="date_to"   value="{{ request('date_to') }}"   class="w-36" placeholder="Đến ngày" />
        <button type="submit"
                class="h-9 px-4 text-sm font-medium rounded-xl transition-colors"
                style="background:var(--surface-bg);border:1px solid var(--surface-border);color:var(--text-secondary)"
                onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
            <i class="ph ph-magnifying-glass mr-1"></i> Lọc
        </button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ngày</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Số phiếu</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Loại</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SL</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn trước</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tồn sau</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Giá vốn</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgers as $row)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $row->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3.5">
                        @if($row->transaction_id)
                        <a href="{{ route('transactions.show', $row->transaction_id) }}"
                           class="font-mono text-xs font-semibold hover:underline" style="color:var(--sidebar-accent)">
                            {{ $row->transaction?->code }}
                        </a>
                        @else
                        <span class="text-xs" style="color:var(--text-muted)">Kiểm kê</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm font-medium" style="color:var(--text-primary)">{{ $row->product?->name }}</td>
                    <td class="px-5 py-3.5 text-center">
                        @php
                        $badgeClass = match($row->type) {
                            'IN'         => 'badge-blue',
                            'OUT'        => 'badge-orange',
                            'ADJUSTMENT' => 'badge-purple',
                            default      => 'badge-gray',
                        };
                        $label = match($row->type) {
                            'IN' => 'Nhập', 'OUT' => 'Xuất', 'ADJUSTMENT' => 'Điều chỉnh', default => $row->type,
                        };
                        @endphp
                        <span class="{{ $badgeClass }} inline-flex px-2 py-0.5 rounded-full text-xs font-medium">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-sm {{ $row->qty >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                        {{ $row->qty >= 0 ? '+' : '' }}{{ number_format($row->qty, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs tabular-nums" style="color:var(--text-muted)">{{ number_format($row->before_qty, 0, ',', '.') }}</td>
                    <td class="px-5 py-3.5 text-right text-sm font-medium tabular-nums" style="color:var(--text-primary)">{{ number_format($row->after_qty, 0, ',', '.') }}</td>
                    <td class="px-5 py-3.5 text-right text-xs tabular-nums" style="color:var(--text-muted)">{{ number_format($row->cost_price, 0, ',', '.') }}đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <i class="ph ph-book-open text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Chưa có lịch sử biến động</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ledgers->hasPages())
    <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
        {{ $ledgers->links() }}
    </div>
    @endif
</div>

@endsection
