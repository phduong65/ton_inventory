@extends('layouts.admin')

@section('title', 'Báo cáo xuất kho')
@section('page-title', 'Báo cáo xuất kho')
@section('breadcrumb', 'Báo cáo / Xuất kho')

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
            <label class="text-xs text-gray-500 dark:text-gray-400">Điểm nhận</label>
            <select name="destination_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-40">
                <option value="">Tất cả</option>
                @foreach($destinations as $dest)
                <option value="{{ $dest->id }}" {{ request('destination_id') == $dest->id ? 'selected' : '' }}>{{ $dest->name }}</option>
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
            <a href="{{ route('reports.issues.export', request()->query()) }}"
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
            Tổng SL: <strong class="text-gray-900 dark:text-white">{{ number_format($rows->sum(fn($r) => abs($r->qty)), 0, ',', '.') }}</strong>
        </span>
        <span class="text-gray-600 dark:text-gray-400">
            Tổng giá trị: <strong class="text-red-600 dark:text-red-400">{{ number_format($rows->sum(fn($r) => abs($r->qty) * $r->cost_price), 0, ',', '.') }}đ</strong>
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
                    <th class="px-4 py-3">Điểm nhận</th>
                    <th class="px-4 py-3">Sản phẩm</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">SL xuất</th>
                    <th class="px-4 py-3 text-right">Giá vốn</th>
                    <th class="px-4 py-3 text-right">Giá trị</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($rows as $row)
                @php $qty = abs($row->qty); $value = $qty * $row->cost_price; @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ $row->transaction?->date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-2.5">
                        <a href="{{ route('transactions.show', $row->transaction_id) }}"
                           class="font-mono text-xs text-primary-600 dark:text-primary-400 hover:underline">
                            {{ $row->transaction?->code }}
                        </a>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $row->transaction?->destination?->name === 'Kho 43' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}">
                            {{ $row->transaction?->destination?->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $row->product?->name }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $row->product?->sku }}</div>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $row->product?->unit }}</td>
                    <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($qty, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-400">{{ number_format($row->cost_price, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($value, 0, ',', '.') }}đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
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
                    <td class="px-4 py-3 text-right">{{ number_format($rows->sum(fn($r) => abs($r->qty)), 0, ',', '.') }}</td>
                    <td></td>
                    <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">
                        {{ number_format($rows->sum(fn($r) => abs($r->qty) * $r->cost_price), 0, ',', '.') }}đ
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@push('scripts')
<script>
flatpickr('#date_from', { dateFormat: 'Y-m-d' });
flatpickr('#date_to',   { dateFormat: 'Y-m-d' });
</script>
@endpush
@endsection
