@extends('layouts.admin')

@section('title', 'Kiểm kê kho')
@section('page-title', 'Kiểm kê kho')
@section('breadcrumb', 'Kiểm kê / Danh sách')

@section('content')

{{-- Quick-create buttons per destination --}}
@can('create-stocktakes')
<div class="flex flex-wrap items-center gap-2 mb-4">
    <a href="{{ route('stocktakes.create') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium rounded-lg">
        <i class="bi bi-plus-lg"></i> Kiểm kê Kho Tổng
    </a>
    @foreach($destinations as $dest)
    <a href="{{ route('stocktakes.create', ['destination_id' => $dest->id]) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
        <i class="bi bi-plus-lg"></i> Kiểm kê {{ $dest->name }}
    </a>
    @endforeach
</div>
@endcan

{{-- Filter bar --}}
<form method="GET" class="flex flex-wrap gap-2 mb-4 items-center">
    <select name="destination_id"
            class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            onchange="this.form.submit()">
        <option value="">Tất cả kho</option>
        <option value="0" {{ request('destination_id') === '0' ? 'selected' : '' }}>Kho Tổng (40)</option>
        @foreach($destinations as $dest)
        <option value="{{ $dest->id }}" {{ request('destination_id') == $dest->id ? 'selected' : '' }}>
            {{ $dest->name }}
        </option>
        @endforeach
    </select>
    <select name="status"
            class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            onchange="this.form.submit()">
        <option value="">Tất cả trạng thái</option>
        <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Nháp</option>
        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Chờ duyệt</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
    </select>
    @if(request('destination_id') || request('status'))
    <a href="{{ route('stocktakes.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
        <i class="bi bi-x-circle"></i> Xóa bộ lọc
    </a>
    @endif
    <span class="text-sm text-gray-500 dark:text-gray-400 ml-auto">{{ $stocktakes->total() }} phiếu</span>
</form>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-400">
    {{ session('success') }}
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-gray-50 dark:bg-gray-700/60 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3">Mã phiếu</th>
                    <th class="px-4 py-3">Kho kiểm kê</th>
                    <th class="px-4 py-3">Ngày tạo</th>
                    <th class="px-4 py-3">Người tạo</th>
                    <th class="px-4 py-3">Phạm vi</th>
                    <th class="px-4 py-3 text-center">Số SP</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3">Ghi chú</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($stocktakes as $st)
                @php
                $badge = match($st->status) {
                    'draft'    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    'pending'  => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                    default    => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                };
                $label = match($st->status) {
                    'draft'    => 'Nháp',
                    'pending'  => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    default    => $st->status,
                };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="px-4 py-3 font-mono text-sm font-medium text-gray-900 dark:text-white">
                        <a href="{{ route('stocktakes.show', $st) }}" class="hover:text-primary-600">{{ $st->code }}</a>
                    </td>
                    <td class="px-4 py-3">
                        @if($st->destination)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <i class="ph ph-warehouse text-xs"></i> {{ $st->destination->name }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                            <i class="ph ph-warehouse text-xs"></i> Kho Tổng
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ $st->created_at->format('d/m/Y') }}
                        <span class="text-xs text-gray-400">{{ $st->created_at->format('H:i') }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $st->createdBy?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($st->destination)
                        <span class="text-xs text-gray-400">—</span>
                        @elseif($st->category)
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                            {{ $st->category->name }}
                        </span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            Tổng kho
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 tabular-nums">
                        {{ $st->details_count }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs max-w-[180px] truncate">
                        {{ $st->note ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('stocktakes.show', $st) }}"
                           class="inline-flex items-center gap-1 text-xs text-primary-600 hover:text-primary-700 font-medium">
                            Xem <i class="bi bi-chevron-right text-[10px]"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center gap-2 text-gray-400">
                            <i class="ph ph-clipboard-text text-4xl"></i>
                            <p class="text-sm">Chưa có phiếu kiểm kê nào</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stocktakes->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $stocktakes->links() }}</div>
    @endif
</div>
@endsection
