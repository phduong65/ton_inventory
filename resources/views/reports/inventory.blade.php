@extends('layouts.admin')

@section('title', 'Báo cáo tồn kho')
@section('page-title', 'Báo cáo tồn kho')
@section('breadcrumb', 'Báo cáo / Tồn kho')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- Filter --}}
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Tồn tại ngày</label>
            <input type="text" name="as_of" value="{{ $asOf }}" id="as_of"
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-40">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                <i class="bi bi-search mr-1"></i> Xem báo cáo
            </button>
            @if($asOf >= $today)
            <a href="{{ route('inventory.index') }}"
               class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-lg flex items-center gap-1">
                <i class="bi bi-arrow-right-circle"></i> Xem tồn kho
            </a>
            @endif
        </div>
    </form>

    {{-- Info bar --}}
    <div class="px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm">
        <span class="text-green-700 dark:text-green-400">
            @if($asOf >= $today)
                Tồn kho hiện tại
            @else
                Tồn kho tại ngày {{ \Carbon\Carbon::parse($asOf)->format('d/m/Y') }}
            @endif
        </span>
        <span class="font-semibold text-green-700 dark:text-green-400">
            Tổng giá trị: {{ number_format($totalValue, 0, ',', '.') }}đ
        </span>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Sản phẩm</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">ĐVT</th>
                    <th class="px-4 py-3 text-right">SL tồn</th>
                    <th class="px-4 py-3 text-right">Giá vốn</th>
                    <th class="px-4 py-3 text-right">Giá trị tồn</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $item->product?->sku }}</td>
                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $item->product?->name }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $item->product?->category?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $item->product?->unit }}</td>
                    <td class="px-4 py-2.5 text-right font-medium {{ $item->quantity > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                        {{ number_format($item->quantity, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-gray-500">{{ number_format($item->average_cost, 0, ',', '.') }}đ</td>
                    <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white">
                        {{ number_format($item->quantity * $item->average_cost, 0, ',', '.') }}đ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph-archive text-4xl block mb-2"></i>
                        Không có dữ liệu tồn kho tại thời điểm này
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($items->count())
            <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold text-gray-700 dark:text-gray-300 text-sm">
                <tr>
                    <td colspan="4" class="px-4 py-3">Tổng cộng ({{ number_format($items->count()) }} sản phẩm)</td>
                    <td class="px-4 py-3 text-right">{{ number_format($items->sum('quantity'), 0, ',', '.') }}</td>
                    <td></td>
                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">{{ number_format($totalValue, 0, ',', '.') }}đ</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@push('scripts')
<script>
flatpickr('#as_of', { dateFormat: 'Y-m-d', maxDate: 'today' });
</script>
@endpush
@endsection
