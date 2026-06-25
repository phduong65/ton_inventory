@extends('layouts.admin')

@section('title', 'Phiếu kiểm kê ' . $stocktake->code)
@section('page-title', $stocktake->code)
@section('breadcrumb', 'Kiểm kê / Chi tiết')

@section('content')
@php
$badge = match($stocktake->status) {
    'draft'    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    'pending'  => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    default    => 'bg-gray-100 text-gray-500',
};
$label = match($stocktake->status) {
    'draft'    => 'Nháp',
    'pending'  => 'Chờ duyệt',
    'approved' => 'Đã duyệt',
    'rejected' => 'Từ chối',
    default    => $stocktake->status,
};
@endphp

<div class="space-y-4">

    {{-- ── Compact Header ──────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            {{-- Left: code + badges --}}
            <div class="flex items-center gap-2 flex-wrap min-w-0">
                <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $stocktake->code }}</span>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ $label }}</span>
                @if($stocktake->destination)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    <i class="ph ph-warehouse"></i> {{ $stocktake->destination->name }}
                </span>
                @elseif($stocktake->category)
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                    {{ $stocktake->category->name }}
                </span>
                @else
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                    Kho Tổng
                </span>
                @endif
                @if($stocktake->note)
                <span class="text-xs text-gray-400 truncate max-w-xs" title="{{ $stocktake->note }}">
                    · {{ $stocktake->note }}
                </span>
                @endif
            </div>

            {{-- Right: action buttons --}}
            <div class="flex gap-2 flex-shrink-0 flex-wrap">
                <a href="{{ route('stocktakes.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="bi bi-arrow-left"></i> Danh sách
                </a>

                <a href="{{ route('stocktakes.print', $stocktake) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="bi bi-printer"></i> In
                </a>

                @if($stocktake->isDraft())
                    @can('create-stocktakes')
                    <form action="{{ route('stocktakes.submit', $stocktake) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                            <i class="bi bi-send"></i> Gửi chờ duyệt
                        </button>
                    </form>
                    @endcan
                    @can('create-stocktakes')
                    <form action="{{ route('stocktakes.destroy', $stocktake) }}" method="POST"
                          onsubmit="return confirm('Xóa phiếu kiểm kê này?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-red-50 text-red-600 hover:bg-red-100 rounded-lg border border-red-200">
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </form>
                    @endcan
                @endif

                @if($stocktake->isPending())
                    @can('approve-stocktakes')
                    <form action="{{ route('stocktakes.approve', $stocktake) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
                            <i class="bi bi-check-circle"></i> Duyệt
                        </button>
                    </form>
                    @endcan
                    @can('reject-stocktakes')
                    <button x-data @click="$dispatch('open-reject-stocktake')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-red-300 text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="bi bi-x-circle"></i> Từ chối
                    </button>
                    @endcan
                @endif
            </div>
        </div>

        @if($stocktake->isRejected() && $stocktake->rejected_reason)
        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">
            <strong>Lý do từ chối:</strong> {{ $stocktake->rejected_reason }}
        </div>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-400">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Detail table ─────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-data="{ search: '' }">

        {{-- Table toolbar: search full-width + summary stats --}}
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
            {{-- Search input — chiếm phần lớn không gian --}}
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs pointer-events-none text-gray-400"></i>
                <input x-model="search"
                       type="text"
                       placeholder="Tìm sản phẩm, SKU..."
                       class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            {{-- Variance summary (compact) --}}
            @php
                $totalVariance = $stocktake->details->sum('variance');
                $countNeg      = $stocktake->details->where('variance', '<', 0)->count();
                $countPos      = $stocktake->details->where('variance', '>', 0)->count();
            @endphp
            <div class="flex items-center gap-2 text-xs flex-shrink-0">
                @if($countPos > 0)
                <span class="text-green-600 font-medium whitespace-nowrap">+{{ $countPos }} tăng</span>
                @endif
                @if($countNeg > 0)
                <span class="text-red-600 font-medium whitespace-nowrap">{{ $countNeg }} giảm</span>
                @endif
                <span class="font-semibold whitespace-nowrap {{ $totalVariance > 0 ? 'text-green-600' : ($totalVariance < 0 ? 'text-red-600' : 'text-gray-400') }}">
                    CL: {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance, 0, ',', '.') }}
                </span>
            </div>
        </div>

        @if($stocktake->details->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
            <i class="ph ph-clipboard-text text-4xl mb-3"></i>
            <p class="text-sm">Phiếu chưa có sản phẩm nào.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-700/60">
                    <tr class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-10">#</th>
                        <th class="px-4 py-3 text-left">Sản phẩm</th>
                        <th class="px-4 py-3 text-left w-20">ĐVT</th>
                        <th class="px-4 py-3 text-right w-32">{{ $stocktake->destination ? 'Đã nhận (HT)' : 'Tồn hệ thống' }}</th>
                        <th class="px-4 py-3 text-right w-32">Thực tế</th>
                        <th class="px-4 py-3 text-right w-32">Chênh lệch</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($stocktake->details as $i => $detail)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40"
                        x-show="!search ||
                            '{{ strtolower($detail->product?->name ?? '') }}'.includes(search.toLowerCase()) ||
                            '{{ strtolower($detail->product?->sku ?? '') }}'.includes(search.toLowerCase())">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $detail->product?->name ?? '—' }}</span>
                            @if($detail->product?->sku)
                            <span class="ml-1.5 text-xs text-gray-400">{{ $detail->product->sku }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                            {{ $detail->product?->unit?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 tabular-nums">
                            {{ number_format($detail->system_qty, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-800 dark:text-gray-200 tabular-nums">
                            {{ number_format($detail->actual_qty, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold tabular-nums
                            {{ $detail->variance > 0 ? 'text-green-600 dark:text-green-400' : ($detail->variance < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400') }}">
                            {{ $detail->variance > 0 ? '+' : '' }}{{ number_format($detail->variance, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Reject Stocktake Modal --}}
<div x-data="{ open: false, reason: '' }"
     x-on:open-reject-stocktake.window="open = true"
     x-show="open"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
     x-cloak>
    <div @click.outside="open = false"
         class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Từ chối phiếu kiểm kê</h3>
        <form action="{{ route('stocktakes.reject', $stocktake) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Lý do từ chối <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" x-model="reason" rows="3" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="Nhập lý do từ chối..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    Hủy
                </button>
                <button type="submit" :disabled="!reason.trim()"
                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                    Xác nhận từ chối
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
