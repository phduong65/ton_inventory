@extends('layouts.admin')

@section('title', 'Phiếu kiểm kê ' . $stocktake->code)
@section('page-title', $stocktake->code)
@section('breadcrumb', 'Kiểm kê / Chi tiết')

@section('content')
@php
$statusBadge = match($stocktake->status) {
    'draft'    => 'badge-gray', 'pending' => 'badge-yellow',
    'approved' => 'badge-green', 'rejected' => 'badge-red', default => 'badge-gray',
};
$statusLabel = match($stocktake->status) {
    'draft' => 'Nháp', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', default => $stocktake->status,
};
@endphp

<div class="space-y-4">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 px-5 py-4"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2 flex-wrap min-w-0">
                <span class="font-mono font-bold text-base" style="color:var(--text-primary)">{{ $stocktake->code }}</span>
                <span class="{{ $statusBadge }} inline-flex px-2 py-0.5 rounded-full text-xs font-semibold">{{ $statusLabel }}</span>
                @if($stocktake->destination)
                <span class="badge-blue inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold">
                    <i class="ph ph-warehouse text-xs"></i> {{ $stocktake->destination->name }}
                </span>
                @elseif($stocktake->category)
                <span class="badge-purple inline-flex px-2 py-0.5 rounded-full text-xs font-semibold">{{ $stocktake->category->name }}</span>
                @else
                <span class="badge-purple inline-flex px-2 py-0.5 rounded-full text-xs font-semibold">Kho Tổng</span>
                @endif
                @if($stocktake->note)
                <span class="text-xs truncate max-w-xs" style="color:var(--text-muted)" title="{{ $stocktake->note }}">
                    · {{ $stocktake->note }}
                </span>
                @endif
            </div>

            <div class="flex gap-2 flex-shrink-0 flex-wrap">
                <a href="{{ route('stocktakes.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                   style="border-color:var(--surface-border);color:var(--text-secondary)"
                   onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-arrow-left text-sm"></i> Danh sách
                </a>

                <a href="{{ route('stocktakes.print', $stocktake) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                   style="border-color:var(--surface-border);color:var(--text-secondary)"
                   onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-printer text-sm"></i> In
                </a>

                @if($stocktake->isDraft())
                    @can('create-stocktakes')
                    <form action="{{ route('stocktakes.submit', $stocktake) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white rounded-xl transition-colors"
                                style="background:#d97706" onmouseover="this.style.background='#b45309'" onmouseout="this.style.background='#d97706'">
                            <i class="ph ph-paper-plane-tilt text-sm"></i> Gửi chờ duyệt
                        </button>
                    </form>
                    @endcan
                    @can('create-stocktakes')
                    <form action="{{ route('stocktakes.destroy', $stocktake) }}" method="POST" onsubmit="return confirm('Xóa phiếu kiểm kê này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                                style="border-color:rgba(239,68,68,0.4);color:#ef4444;background:rgba(239,68,68,0.05)"
                                onmouseover="this.style.background='rgba(239,68,68,0.10)'" onmouseout="this.style.background='rgba(239,68,68,0.05)'">
                            <i class="ph ph-trash text-sm"></i> Xóa
                        </button>
                    </form>
                    @endcan
                @endif

                @if($stocktake->isPending())
                    @can('approve-stocktakes')
                    <form action="{{ route('stocktakes.approve', $stocktake) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white rounded-xl transition-colors"
                                style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                            <i class="ph ph-check-circle text-sm"></i> Duyệt
                        </button>
                    </form>
                    @endcan
                    @can('reject-stocktakes')
                    <button x-data @click="$dispatch('open-reject-stocktake')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                            style="border-color:rgba(239,68,68,0.4);color:#ef4444;background:rgba(239,68,68,0.05)"
                            onmouseover="this.style.background='rgba(239,68,68,0.10)'" onmouseout="this.style.background='rgba(239,68,68,0.05)'">
                        <i class="ph ph-x-circle text-sm"></i> Từ chối
                    </button>
                    @endcan
                @endif
            </div>
        </div>

        @if($stocktake->isRejected() && $stocktake->rejected_reason)
        <div class="mt-3 p-3 rounded-xl text-sm" style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.20);color:#b91c1c">
            <strong>Lý do từ chối:</strong> {{ $stocktake->rejected_reason }}
        </div>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="rounded-xl px-4 py-3 text-sm" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#059669">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-xl px-4 py-3 text-sm" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);color:#dc2626">
        {{ session('error') }}
    </div>
    @endif

    {{-- Detail table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)"
         x-data="{ search: '' }">

        <div class="px-4 py-3 flex items-center gap-3" style="border-bottom:1px solid var(--surface-border)">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm pointer-events-none" style="color:var(--text-muted)"></i>
                <input x-model="search" type="text" placeholder="Tìm sản phẩm, SKU..."
                       class="form-input pl-9 w-full text-sm">
            </div>
            @php
            $totalVariance = $stocktake->details->sum('variance');
            $countNeg      = $stocktake->details->where('variance', '<', 0)->count();
            $countPos      = $stocktake->details->where('variance', '>', 0)->count();
            @endphp
            <div class="flex items-center gap-3 text-xs flex-shrink-0">
                @if($countPos > 0)
                <span class="font-semibold" style="color:#16a34a">+{{ $countPos }} tăng</span>
                @endif
                @if($countNeg > 0)
                <span class="font-semibold" style="color:#ef4444">{{ $countNeg }} giảm</span>
                @endif
                <span class="font-semibold" style="color:{{ $totalVariance > 0 ? '#16a34a' : ($totalVariance < 0 ? '#ef4444' : 'var(--text-muted)') }}">
                    CL: {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance, 0, ',', '.') }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold" style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $stocktake->details->count() }} sản phẩm</span>
            </div>
        </div>

        @if($stocktake->details->isEmpty())
        <div class="flex flex-col items-center justify-center py-16">
            <i class="ph ph-clipboard-text text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
            <p class="text-sm" style="color:var(--text-muted)">Phiếu chưa có sản phẩm nào.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide w-10" style="color:var(--text-muted)">#</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide w-20" style="color:var(--text-muted)">ĐVT</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide w-32" style="color:var(--text-muted)">{{ $stocktake->destination ? 'Đã nhận (HT)' : 'Tồn hệ thống' }}</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide w-32" style="color:var(--text-muted)">Thực tế</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide w-32" style="color:var(--text-muted)">Chênh lệch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocktake->details as $i => $detail)
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors"
                        x-show="!search ||
                            '{{ strtolower($detail->product?->name ?? '') }}'.includes(search.toLowerCase()) ||
                            '{{ strtolower($detail->product?->sku ?? '') }}'.includes(search.toLowerCase())">
                        <td class="px-4 py-3 text-xs tabular-nums" style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium" style="color:var(--text-primary)">{{ $detail->product?->name ?? '—' }}</span>
                            @if($detail->product?->sku)
                            <span class="ml-1.5 text-xs font-mono" style="color:var(--text-muted)">{{ $detail->product->sku }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $detail->product?->unit?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums" style="color:var(--text-muted)">{{ number_format($detail->system_qty, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium tabular-nums" style="color:var(--text-primary)">{{ number_format($detail->actual_qty, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold tabular-nums"
                            style="color:{{ $detail->variance > 0 ? '#16a34a' : ($detail->variance < 0 ? '#ef4444' : 'var(--text-muted)') }}">
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
@can('reject-stocktakes')
@if($stocktake->isPending())
<div x-data="{ open: false, reason: '' }" x-on:open-reject-stocktake.window="open = true">
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="open = false"></div>
        <div class="modal-panel relative w-full max-w-md p-5"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(239,68,68,0.10)">
                    <i class="ph ph-x-circle text-sm" style="color:#ef4444"></i>
                </div>
                <h3 class="font-semibold text-base" style="color:var(--text-primary)">Từ chối phiếu kiểm kê</h3>
            </div>
            <form action="{{ route('stocktakes.reject', $stocktake) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Lý do từ chối <span class="text-red-500 normal-case">*</span></label>
                    <textarea name="reason" x-model="reason" rows="3" required placeholder="Nhập lý do từ chối..." class="form-input resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                    <button type="submit" :disabled="!reason.trim()" class="px-4 py-2 text-sm font-medium text-white rounded-xl disabled:opacity-50" style="background:#ef4444" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan

@endsection
