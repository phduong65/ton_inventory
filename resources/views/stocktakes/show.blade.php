@extends('layouts.admin')

@section('title', 'Phiếu kiểm kê ' . $stocktake->code)
@section('page-title', 'Phiếu kiểm kê ' . $stocktake->code)
@section('breadcrumb', 'Kiểm kê / ' . $stocktake->code)

@section('content')
<div class="space-y-5">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white font-mono">{{ $stocktake->code }}</h2>
                    @php
                    $badge = match($stocktake->status) {
                        'draft' => 'bg-gray-100 text-gray-700', 'pending' => 'bg-yellow-100 text-yellow-700',
                        'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700',
                    };
                    $label = match($stocktake->status) {
                        'draft' => 'Nháp', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối',
                    };
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $label }}</span>
                </div>
                <p class="text-sm text-gray-500">Tạo: {{ $stocktake->created_at->format('d/m/Y H:i') }} bởi {{ $stocktake->createdBy?->name }}</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xs text-gray-500">Phạm vi:</span>
                    @if($stocktake->category)
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                        {{ $stocktake->category->name }}
                    </span>
                    @else
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                        Tổng (tất cả)
                    </span>
                    @endif
                </div>
            </div>

            <div class="flex gap-2">
                @if($stocktake->status === 'draft')
                    @can('create-stocktakes')
                    <form action="{{ route('stocktakes.submit', $stocktake) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                            <i class="bi bi-send"></i> Submit
                        </button>
                    </form>
                    @endcan
                @endif
                @if($stocktake->status === 'pending')
                    @can('approve-stocktakes')
                    <form action="{{ route('stocktakes.approve', $stocktake) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
                            <i class="bi bi-check-circle"></i> Duyệt
                        </button>
                    </form>
                    @endcan
                @endif
            </div>
        </div>

        @if($stocktake->note)
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Ghi chú: {{ $stocktake->note }}</p>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Chi tiết kiểm kê</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Sản phẩm</th>
                    <th class="px-4 py-3 text-left">ĐVT</th>
                    <th class="px-4 py-3 text-right">Tồn hệ thống</th>
                    <th class="px-4 py-3 text-right">Thực tế</th>
                    <th class="px-4 py-3 text-right">Chênh lệch</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($stocktake->details as $i => $detail)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $detail->product?->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $detail->product?->unit }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ number_format($detail->system_qty, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($detail->actual_qty, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-medium {{ $detail->variance > 0 ? 'text-green-600' : ($detail->variance < 0 ? 'text-red-600' : 'text-gray-400') }}">
                        {{ $detail->variance >= 0 ? '+' : '' }}{{ number_format($detail->variance, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
