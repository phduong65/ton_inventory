@extends('layouts.admin')

@section('title', 'Phiếu nhập / xuất')
@section('page-title', 'Phiếu nhập / xuất kho')
@section('breadcrumb', 'Phiếu NK/XK')

@section('content')
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $transactions->total() }} phiếu</p>
    <div class="flex gap-2">
        @can('create-transactions')
        <a href="{{ route('transactions.create', ['type' => 'IN']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
            <i class="bi bi-download"></i> Tạo phiếu nhập
        </a>
        <a href="{{ route('transactions.create', ['type' => 'OUT']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg">
            <i class="bi bi-upload"></i> Tạo phiếu xuất
        </a>
        @endcan
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
        <select name="type" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Tất cả loại</option>
            <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Nhập kho</option>
            <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Xuất kho</option>
        </select>
        <select name="status" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value="">Tất cả trạng thái</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Nháp</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
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
                    <th class="px-4 py-3">Số phiếu</th>
                    <th class="px-4 py-3">Loại</th>
                    <th class="px-4 py-3">Ngày</th>
                    <th class="px-4 py-3">Đối tác</th>
                    <th class="px-4 py-3">Người tạo</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($transactions as $tx)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3">
                        <a href="{{ route('transactions.show', $tx) }}" class="font-mono text-xs text-primary-600 hover:underline font-medium">
                            {{ $tx->code }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        @if($tx->type === 'IN')
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                <i class="bi bi-download mr-1"></i> Nhập
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                <i class="bi bi-upload mr-1"></i> Xuất
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $tx->date?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                        {{ $tx->supplier?->name ?? $tx->destination?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $tx->createdBy?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @php
                        $badge = match($tx->status) {
                            'draft'    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            'pending'  => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                        };
                        $label = match($tx->status) {
                            'draft'    => 'Nháp',
                            'pending'  => 'Chờ duyệt',
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Từ chối',
                        };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('transactions.show', $tx) }}" class="text-xs text-primary-600 hover:underline">Xem</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph-clipboard-text text-4xl block mb-2"></i>
                        Chưa có phiếu nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection
