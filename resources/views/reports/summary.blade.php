@extends('layouts.admin')

@section('title', 'Báo cáo nhập xuất tồn')
@section('page-title', 'Báo cáo nhập xuất tồn')
@section('breadcrumb', 'Báo cáo / Nhập xuất tồn')

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

        {{-- Kỳ nhanh --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Kỳ nhanh</label>
            <div class="flex gap-1">
                @php
                    $quickRanges = [
                        'Tháng này'   => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
                        'Tháng trước' => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
                        'Quý này'     => [now()->startOfQuarter()->format('Y-m-d'), now()->format('Y-m-d')],
                        'Năm nay'     => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
                    ];
                @endphp
                @foreach($quickRanges as $label => [$qFrom, $qTo])
                <a href="{{ route('reports.summary', ['date_from' => $qFrom, 'date_to' => $qTo]) }}"
                   class="px-2 py-1.5 text-xs border rounded {{ $from === $qFrom && $to === $qTo ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                <i class="bi bi-search mr-1"></i> Xem báo cáo
            </button>
            @can('export-reports')
            <a href="{{ route('reports.summary.export', request()->query()) }}"
               class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center gap-1">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            @endcan
        </div>
    </form>

    {{-- Period header --}}
    <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
        Kỳ báo cáo: <strong class="text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</strong>
        — <strong class="text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</strong>
        &nbsp;·&nbsp; {{ $rows->count() }} sản phẩm
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Sản phẩm</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">Tồn đầu kỳ</th>
                    <th class="px-4 py-3 text-right text-green-600 dark:text-green-400">Nhập</th>
                    <th class="px-4 py-3 text-right text-red-500 dark:text-red-400">Xuất</th>
                    <th class="px-4 py-3 text-right text-yellow-600 dark:text-yellow-400">Điều chỉnh</th>
                    <th class="px-4 py-3 text-right">Tồn cuối kỳ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($rows as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $row['product']?->sku }}</td>
                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                        {{ $row['product']?->name }}
                        <span class="text-xs text-gray-400 ml-1">{{ $row['product']?->category?->name }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $row['product']?->unit }}</td>
                    <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-400">{{ number_format($row['openQty'], 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-medium {{ $row['inQty'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                        {{ $row['inQty'] > 0 ? '+'.number_format($row['inQty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-medium {{ $row['outQty'] > 0 ? 'text-red-500 dark:text-red-400' : 'text-gray-400' }}">
                        {{ $row['outQty'] > 0 ? number_format($row['outQty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right {{ $row['adjQty'] != 0 ? ($row['adjQty'] > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-orange-600 dark:text-orange-400') : 'text-gray-400' }}">
                        {{ $row['adjQty'] != 0 ? ($row['adjQty'] > 0 ? '+' : '').number_format($row['adjQty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-semibold {{ $row['closeQty'] > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                        {{ number_format($row['closeQty'], 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph-chart-bar text-4xl block mb-2"></i>
                        Không có dữ liệu trong kỳ này
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-300 text-sm">
                <tr>
                    <td colspan="3" class="px-4 py-3">Tổng cộng</td>
                    <td class="px-4 py-3 text-right">{{ number_format($rows->sum('openQty'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">+{{ number_format($rows->sum('inQty'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-red-500 dark:text-red-400">{{ number_format($rows->sum('outQty'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-yellow-600 dark:text-yellow-400">{{ number_format($rows->sum('adjQty'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($rows->sum('closeQty'), 0, ',', '.') }}</td>
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
