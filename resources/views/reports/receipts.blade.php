@extends('layouts.admin')

@section('title', 'Báo cáo nhập kho')
@section('page-title', 'Báo cáo nhập kho')
@section('breadcrumb', 'Báo cáo / Nhập kho')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- Filter --}}
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Từ ngày</label>
            <input type="text" name="date_from" value="{{ $from }}" id="date_from"
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-36">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Đến ngày</label>
            <input type="text" name="date_to" value="{{ $to }}" id="date_to"
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-36">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Nhà cung cấp</label>
            <select name="supplier_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-48">
                <option value="">Tất cả NCC</option>
                @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Sản phẩm</label>
            <select name="product_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-48">
                <option value="">Tất cả sản phẩm</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                <i class="bi bi-search mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('reports.receipts.export', request()->query()) }}"
               class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center gap-1">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Summary bar --}}
    @if($rows->count())
    <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex gap-6 text-sm">
        <span class="text-gray-600 dark:text-gray-400">
            Tổng dòng: <strong class="text-gray-900 dark:text-white">{{ number_format($rows->count()) }}</strong>
        </span>
        <span class="text-gray-600 dark:text-gray-400">
            Tổng SL: <strong class="text-gray-900 dark:text-white">{{ number_format($rows->sum('qty'), 0, ',', '.') }}</strong>
        </span>
        <span class="text-gray-600 dark:text-gray-400">
            Tổng tiền: <strong class="text-green-600 dark:text-green-400">{{ number_format($rows->sum('amount'), 0, ',', '.') }}đ</strong>
        </span>
    </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">Ngày</th>
                    <th class="px-4 py-3">Số phiếu</th>
                    <th class="px-4 py-3">Nhà cung cấp</th>
                    <th class="px-4 py-3">Sản phẩm</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">SL</th>
                    <th class="px-4 py-3 text-right">Đơn giá</th>
                    <th class="px-4 py-3 text-right">CK%</th>
                    <th class="px-4 py-3 text-right">VAT%</th>
                    <th class="px-4 py-3 text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($rows as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ $row->transaction?->date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-2.5">
                        <a href="{{ route('transactions.show', $row->transaction_id) }}"
                           class="font-mono text-xs text-primary-600 dark:text-primary-400 hover:underline">
                            {{ $row->transaction?->code }}
                        </a>
                    </td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $row->transaction?->supplier?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $row->product?->name }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $row->product?->sku }}</div>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $row->product?->unit }}</td>
                    <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($row->qty, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-400">{{ number_format($row->price, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right text-gray-500">{{ $row->discount > 0 ? $row->discount.'%' : '—' }}</td>
                    <td class="px-4 py-2.5 text-right text-gray-500">{{ $row->vat > 0 ? $row->vat.'%' : '—' }}</td>
                    <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($row->amount, 0, ',', '.') }}đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph-file-text text-4xl block mb-2"></i>
                        Không có dữ liệu trong kỳ này
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-300 text-sm">
                <tr>
                    <td colspan="5" class="px-4 py-3">Tổng cộng</td>
                    <td class="px-4 py-3 text-right">{{ number_format($rows->sum('qty'), 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">{{ number_format($rows->sum('amount'), 0, ',', '.') }}đ</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@push('scripts')
<script>
flatpickr('#date_from', { dateFormat: 'Y-m-d', locale: 'vn' });
flatpickr('#date_to',   { dateFormat: 'Y-m-d', locale: 'vn' });
</script>
@endpush
@endsection
