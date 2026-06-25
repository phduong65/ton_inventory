@extends('layouts.admin')

@section('title', 'Công nợ nội bộ')
@section('page-title', 'Báo cáo công nợ nội bộ')
@section('breadcrumb', 'Báo cáo / Công nợ nội bộ')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- Filter --}}
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Tháng</label>
            <select name="month" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-32">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                @endfor
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Năm</label>
            <select name="year" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-28">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg self-end">
            <i class="bi bi-search mr-1"></i> Xem báo cáo
        </button>
    </form>

    {{-- Period label --}}
    <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm">
        <span class="text-gray-600 dark:text-gray-400">
            Kỳ đối soát: <strong class="text-gray-900 dark:text-white">Tháng {{ $month }}/{{ $year }}</strong>
        </span>
        <span class="font-semibold text-gray-700 dark:text-gray-300">
            Tổng giá trị xuất: <span class="text-red-600 dark:text-red-400">{{ number_format($grandTotal, 0, ',', '.') }}đ</span>
        </span>
    </div>

    {{-- Destination totals summary --}}
    @if($destinations->count())
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex gap-6">
        @foreach($destinations as $destId => $dest)
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg px-4 py-2.5 flex flex-col gap-0.5">
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $dest->name }}</span>
            <span class="font-semibold text-gray-900 dark:text-white text-sm">
                {{ number_format($destTotals[$destId]['value'] ?? 0, 0, ',', '.') }}đ
            </span>
            <span class="text-xs text-gray-500">SL: {{ number_format($destTotals[$destId]['qty'] ?? 0, 0, ',', '.') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3" rowspan="2">Sản phẩm</th>
                    <th class="px-4 py-3 text-center" rowspan="2">ĐVT</th>
                    @foreach($destinations as $dest)
                    <th class="px-4 py-3 text-center border-l border-gray-200 dark:border-gray-600" colspan="2">{{ $dest->name }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right border-l border-gray-200 dark:border-gray-600">Tổng giá trị</th>
                </tr>
                <tr>
                    @foreach($destinations as $dest)
                    <th class="px-4 py-2 text-right border-l border-gray-200 dark:border-gray-600 font-normal normal-case">SL</th>
                    <th class="px-4 py-2 text-right font-normal normal-case">Giá trị</th>
                    @endforeach
                    <th class="border-l border-gray-200 dark:border-gray-600"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($reportRows as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2.5">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $row['product']?->name }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $row['product']?->sku }}</div>
                    </td>
                    <td class="px-4 py-2.5 text-center text-gray-500">{{ $row['product']?->unit?->name ?? '—' }}</td>
                    @foreach($destinations as $destId => $dest)
                    @php $d = $row['destData'][$destId] ?? ['qty' => 0, 'value' => 0]; @endphp
                    <td class="px-4 py-2.5 text-right border-l border-gray-100 dark:border-gray-700 {{ $d['qty'] > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-300 dark:text-gray-600' }}">
                        {{ $d['qty'] > 0 ? number_format($d['qty'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right {{ $d['value'] > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-300 dark:text-gray-600' }}">
                        {{ $d['value'] > 0 ? number_format($d['value'], 0, ',', '.') : '—' }}
                    </td>
                    @endforeach
                    <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white border-l border-gray-100 dark:border-gray-700">
                        {{ number_format($row['totalValue'], 0, ',', '.') }}đ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 2 + $destinations->count() * 2 + 1 }}" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph ph-scales text-4xl block mb-2"></i>
                        Không có dữ liệu xuất kho trong tháng {{ $month }}/{{ $year }}
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($reportRows->count())
            <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-300 text-sm">
                <tr>
                    <td colspan="2" class="px-4 py-3">Tổng cộng</td>
                    @foreach($destinations as $destId => $dest)
                    @php $dt = $destTotals[$destId] ?? ['qty' => 0, 'value' => 0]; @endphp
                    <td class="px-4 py-3 text-right border-l border-gray-200 dark:border-gray-600">{{ number_format($dt['qty'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($dt['value'], 0, ',', '.') }}đ</td>
                    @endforeach
                    <td class="px-4 py-3 text-right text-red-600 dark:text-red-400 border-l border-gray-200 dark:border-gray-600">
                        {{ number_format($grandTotal, 0, ',', '.') }}đ
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
