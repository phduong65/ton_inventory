@extends('layouts.admin')

@section('title', 'Kiểm kê kho')
@section('page-title', 'Kiểm kê kho')
@section('breadcrumb', 'Kiểm kê / Danh sách')

@section('content')
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $stocktakes->total() }} phiếu kiểm kê</p>
    @can('create-stocktakes')
    <a href="{{ route('stocktakes.create') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <i class="bi bi-plus-lg"></i> Tạo phiếu kiểm kê
    </a>
    @endcan
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">Mã phiếu</th>
                    <th class="px-4 py-3">Ngày tạo</th>
                    <th class="px-4 py-3">Người tạo</th>
                    <th class="px-4 py-3">Phạm vi</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3">Ghi chú</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($stocktakes as $st)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-mono text-sm font-medium text-gray-900 dark:text-white">{{ $st->code }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $st->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $st->createdBy?->name }}</td>
                    <td class="px-4 py-3">
                        @if($st->category)
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                            {{ $st->category->name }}
                        </span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            Tổng
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                        $badge = match($st->status) {
                            'draft'    => 'bg-gray-100 text-gray-700',
                            'pending'  => 'bg-yellow-100 text-yellow-700',
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                        };
                        $label = match($st->status) {
                            'draft' => 'Nháp', 'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt', 'rejected' => 'Từ chối',
                        };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $st->note ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('stocktakes.show', $st) }}" class="text-xs text-primary-600 hover:underline">Xem</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Chưa có phiếu kiểm kê</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stocktakes->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $stocktakes->links() }}</div>
    @endif
</div>
@endsection
