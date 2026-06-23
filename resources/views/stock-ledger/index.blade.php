@extends('layouts.admin')

@section('title', 'Thẻ kho')
@section('page-title', 'Thẻ kho')
@section('breadcrumb', 'Kho / Thẻ kho')

@section('content')
<div class="mb-4 flex justify-end">
    @can('export-stock-ledger')
    <a href="{{ route('stock-ledger.export', request()->query()) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <i class="bi bi-file-earmark-excel"></i> Xuất Excel
    </a>
    @endcan
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
        <select name="product_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-56">
            <option value="">Tất cả sản phẩm</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="type" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Tất cả loại</option>
            <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Nhập</option>
            <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Xuất</option>
            <option value="ADJUSTMENT" {{ request('type') === 'ADJUSTMENT' ? 'selected' : '' }}>Điều chỉnh</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        <button type="submit" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg">
            <i class="bi bi-search mr-1"></i> Lọc
        </button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">Ngày</th>
                    <th class="px-4 py-3">Số phiếu</th>
                    <th class="px-4 py-3">Sản phẩm</th>
                    <th class="px-4 py-3 text-center">Loại</th>
                    <th class="px-4 py-3 text-right">SL</th>
                    <th class="px-4 py-3 text-right">Tồn trước</th>
                    <th class="px-4 py-3 text-right">Tồn sau</th>
                    <th class="px-4 py-3 text-right">Giá vốn</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($ledgers as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 text-gray-500">{{ $row->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        @if($row->transaction_id)
                        <a href="{{ route('transactions.show', $row->transaction_id) }}" class="font-mono text-xs text-primary-600 hover:underline">
                            {{ $row->transaction?->code }}
                        </a>
                        @else
                        <span class="text-xs text-gray-400">Kiểm kê</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row->product?->name }}</td>
                    <td class="px-4 py-3 text-center">
                        @php
                        $badge = match($row->type) {
                            'IN'         => 'bg-blue-100 text-blue-700',
                            'OUT'        => 'bg-orange-100 text-orange-700',
                            'ADJUSTMENT' => 'bg-purple-100 text-purple-700',
                        };
                        $label = match($row->type) {
                            'IN' => 'Nhập', 'OUT' => 'Xuất', 'ADJUSTMENT' => 'Điều chỉnh',
                        };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium {{ $row->qty >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $row->qty >= 0 ? '+' : '' }}{{ number_format($row->qty, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ number_format($row->before_qty, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($row->after_qty, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ number_format($row->cost_price, 0, ',', '.') }}đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph-book-open text-4xl block mb-2"></i>
                        Chưa có lịch sử biến động
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ledgers->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $ledgers->links() }}
    </div>
    @endif
</div>
@endsection
