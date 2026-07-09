@extends('layouts.admin')

@section('title', 'Phiếu ' . $transaction->code)
@section('page-title', 'Phiếu ' . $transaction->code)
@section('breadcrumb', 'Phiếu NK/XK / ' . $transaction->code)

@php $requireApproval = \App\Models\Setting::get('require_approval', true); @endphp

@section('content')
@php
$typeBadge = $transaction->type === 'IN' ? 'badge-blue' : 'badge-orange';
$typeLabel = $transaction->type === 'IN' ? 'Phiếu nhập' : 'Phiếu xuất';
$statusBadge = match($transaction->status) {
    'draft' => 'badge-gray', 'pending' => 'badge-yellow',
    'approved' => 'badge-green', 'rejected' => 'badge-red', default => 'badge-gray'
};
$statusLabel = match($transaction->status) {
    'draft' => 'Nháp', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', default => $transaction->status
};
@endphp

<div class="space-y-4">

    {{-- Header card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-start justify-between gap-4 flex-wrap mb-5">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <h2 class="text-lg font-bold font-mono" style="color:var(--text-primary)">{{ $transaction->code }}</h2>
                    <span class="{{ $typeBadge }} inline-flex px-2 py-0.5 rounded-full text-xs font-semibold">{{ $typeLabel }}</span>
                    <span class="{{ $statusBadge }} inline-flex px-2 py-0.5 rounded-full text-xs font-semibold">{{ $statusLabel }}</span>
                </div>
                <p class="text-sm" style="color:var(--text-muted)">
                    Ngày: <strong>{{ $transaction->date?->format('d/m/Y') }}</strong>
                    <span class="ml-2 tabular-nums" style="color:var(--text-muted)"><i class="ph ph-clock text-xs mr-0.5"></i>{{ $transaction->created_at?->format('H:i') }}</span>
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 flex-wrap">
                @can('print-transactions')
                <a href="{{ route('transactions.print', $transaction) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                   style="border-color:var(--surface-border);color:var(--text-secondary)"
                   onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-printer text-sm"></i> In
                </a>
                @endcan

                @can('create-transactions')
                <form action="{{ route('transactions.clone', $transaction) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                            style="border-color:var(--surface-border);color:var(--text-secondary)"
                            onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                        <i class="ph ph-copy text-sm"></i> Nhân bản
                    </button>
                </form>
                @endcan

                @if($transaction->isDraft())
                    @can('edit-transactions')
                    <a href="{{ route('transactions.edit', $transaction) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                       style="border-color:var(--surface-border);color:var(--text-secondary)"
                       onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                        <i class="ph ph-pencil-simple text-sm"></i> Sửa
                    </a>
                    @endcan

                    @can('create-transactions')
                    <form action="{{ route('transactions.submit', $transaction) }}" method="POST">
                        @csrf
                        @if($requireApproval)
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white rounded-xl transition-colors"
                                style="background:#d97706" onmouseover="this.style.background='#b45309'" onmouseout="this.style.background='#d97706'">
                            <i class="ph ph-paper-plane-tilt text-sm"></i> Gửi chờ duyệt
                        </button>
                        @else
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white rounded-xl transition-colors"
                                style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                            <i class="ph ph-check-circle text-sm"></i> Xác nhận
                        </button>
                        @endif
                    </form>
                    @endcan

                    @can('delete-transactions')
                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Xóa phiếu này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border border-red-200 dark:border-red-800 transition-colors"
                                style="color:#ef4444;background:rgba(239,68,68,0.05)"
                                onmouseover="this.style.background='rgba(239,68,68,0.10)'" onmouseout="this.style.background='rgba(239,68,68,0.05)'">
                            <i class="ph ph-trash text-sm"></i> Xóa
                        </button>
                    </form>
                    @endcan
                @endif

                @if($transaction->isPending())
                    @if(auth()->user()->hasRole('admin') || $transaction->created_by === auth()->id())
                    <form action="{{ route('transactions.cancel', $transaction) }}" method="POST" onsubmit="return confirm('Hủy phiếu và chuyển về nháp?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                                style="border-color:rgba(249,115,22,0.4);color:#f97316;background:rgba(249,115,22,0.05)"
                                onmouseover="this.style.background='rgba(249,115,22,0.10)'" onmouseout="this.style.background='rgba(249,115,22,0.05)'">
                            <i class="ph ph-prohibit text-sm"></i> Hủy
                        </button>
                    </form>
                    @endif
                @endif

                @if($requireApproval && $transaction->isPending())
                    @can('reject-transactions')
                    <button x-data @click="$dispatch('open-reject')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-xl border transition-colors"
                            style="border-color:rgba(239,68,68,0.4);color:#ef4444;background:rgba(239,68,68,0.05)"
                            onmouseover="this.style.background='rgba(239,68,68,0.10)'" onmouseout="this.style.background='rgba(239,68,68,0.05)'">
                        <i class="ph ph-x-circle text-sm"></i> Từ chối
                    </button>
                    @endcan

                    @can('approve-transactions')
                    <form action="{{ route('transactions.approve', $transaction) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white rounded-xl transition-colors"
                                style="background:#16a34a" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                            <i class="ph ph-check-circle text-sm"></i> Duyệt
                        </button>
                    </form>
                    @endcan
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--text-muted)">{{ $transaction->type === 'IN' ? 'Nhà cung cấp' : 'Điểm nhận' }}</p>
                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $transaction->supplier?->name ?? ($transaction->destination?->name ?? '—') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--text-muted)">Người tạo</p>
                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $transaction->createdBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--text-muted)">Người duyệt</p>
                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $transaction->approvedBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--text-muted)">Ghi chú</p>
                <p class="text-sm" style="color:var(--text-secondary)">{{ $transaction->note ?: '—' }}</p>
            </div>
        </div>

        @if($transaction->isRejected() && $transaction->rejected_reason)
        <div class="mt-4 p-3 rounded-xl text-sm" style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.20);color:#b91c1c">
            <strong>Lý do từ chối:</strong> {{ $transaction->rejected_reason }}
        </div>
        @endif
    </div>

    {{-- Details --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="px-5 py-3.5 flex items-center gap-2" style="border-bottom:1px solid var(--surface-border)">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                <i class="ph ph-list-bullets text-sm" style="color:#4f46e5"></i>
            </div>
            <h3 class="font-semibold text-sm" style="color:var(--text-primary)">Chi tiết sản phẩm</h3>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full ml-1" style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $transaction->details->count() }} dòng</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">#</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">ĐVT</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Số lượng</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Quy đổi</th>
                        @if($transaction->type === 'IN')
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đơn giá</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">CK%</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tiền CK</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">VAT%</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tiền VAT</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Thành tiền</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->details as $i => $detail)
                    @php
                    $baseUnitName = $detail->product?->unit?->name ?? '';
                    $detailUnit = $detail->unit?->name ?? $baseUnitName;
                    $hasConversion = $detail->conversion_factor > 1;
                    @endphp
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                        <td class="px-4 py-3 text-xs tabular-nums" style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium" style="color:var(--text-primary)">{{ $detail->product?->name }}</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $detailUnit }}</td>
                        <td class="px-4 py-3 text-right tabular-nums" style="color:var(--text-primary)">
                            {{ number_format($detail->qty, $detail->qty == floor($detail->qty) ? 0 : 3, ',', '.') }}
                            <span class="text-xs ml-0.5" style="color:var(--text-muted)">{{ $detailUnit }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs tabular-nums" style="color:var(--text-muted)">
                            @if($hasConversion)
                                {{ number_format($detail->base_qty, $detail->base_qty == floor($detail->base_qty) ? 0 : 3, ',', '.') }} {{ $baseUnitName }}
                            @else
                                —
                            @endif
                        </td>
                        @if($transaction->type === 'IN')
                        @php
                        $baseAmt = $detail->qty * $detail->price;
                        $discountAmt = $baseAmt * ($detail->discount / 100);
                        $vatAmt = ($baseAmt - $discountAmt) * ($detail->vat / 100);
                        @endphp
                        <td class="px-4 py-3 text-right tabular-nums text-xs" style="color:var(--text-secondary)">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                        <td class="px-4 py-3 text-right text-xs" style="color:var(--text-muted)">{{ $detail->discount }}%</td>
                        <td class="px-4 py-3 text-right text-xs tabular-nums" style="color:#f97316">
                            @if($discountAmt > 0) - {{ number_format($discountAmt, 0, ',', '.') }}đ @else — @endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs" style="color:var(--text-muted)">{{ $detail->vat }}%</td>
                        <td class="px-4 py-3 text-right text-xs tabular-nums" style="color:#3b82f6">
                            @if($vatAmt > 0) + {{ number_format($vatAmt, 0, ',', '.') }}đ @else — @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold tabular-nums" style="color:var(--text-primary)">{{ number_format($detail->amount, 0, ',', '.') }}đ</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
                @if($transaction->type === 'IN')
                @php
                $totalDiscountAmt = $transaction->details->sum(fn($d) => $d->qty * $d->price * ($d->discount / 100));
                $totalVatAmt = $transaction->details->sum(fn($d) => $d->qty * $d->price * (1 - $d->discount / 100) * ($d->vat / 100));
                @endphp
                <tfoot>
                    <tr style="background:var(--surface-bg);border-top:1px dashed var(--surface-border)">
                        <td colspan="10" class="px-5 py-2 text-right text-xs" style="color:var(--text-muted)">Tổng chiết khấu</td>
                        <td class="px-5 py-2 text-right text-sm font-semibold tabular-nums" style="color:#f97316">
                            @if($totalDiscountAmt > 0) - {{ number_format($totalDiscountAmt, 0, ',', '.') }}đ @else — @endif
                        </td>
                    </tr>
                    <tr style="background:var(--surface-bg);border-top:1px dashed var(--surface-border)">
                        <td colspan="10" class="px-5 py-2 text-right text-xs" style="color:var(--text-muted)">Tổng VAT</td>
                        <td class="px-5 py-2 text-right text-sm font-semibold tabular-nums" style="color:#3b82f6">
                            @if($totalVatAmt > 0) + {{ number_format($totalVatAmt, 0, ',', '.') }}đ @else — @endif
                        </td>
                    </tr>
                    <tr style="background:rgba(22,163,74,0.04);border-top:2px solid var(--surface-border)">
                        <td colspan="10" class="px-5 py-3 text-right text-sm font-semibold" style="color:var(--text-secondary)">TỔNG CỘNG</td>
                        <td class="px-5 py-3 text-right text-base font-bold tabular-nums" style="color:#16a34a">{{ number_format($transaction->details->sum('amount'), 0, ',', '.') }}đ</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Attachments --}}
    @if($transaction->attachments->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)"
         x-data="{
             lightbox: false, current: 0,
             images: {{ $transaction->attachments->filter(fn($a) => $a->isImage())->values()->map(fn($a) => ['url' => $a->url, 'name' => $a->original_name])->toJson() }},
             open(i) { this.current = i; this.lightbox = true; },
             prev() { this.current = (this.current - 1 + this.images.length) % this.images.length; },
             next() { this.current = (this.current + 1) % this.images.length; },
         }"
         @keydown.escape.window="lightbox = false"
         @keydown.arrow-left.window="lightbox && prev()"
         @keydown.arrow-right.window="lightbox && next()">

        <div class="flex items-center gap-2.5 px-5 py-3.5" style="border-bottom:1px solid var(--surface-border)">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                <i class="ph ph-images text-sm" style="color:#4f46e5"></i>
            </div>
            <span class="font-semibold text-sm" style="color:var(--text-primary)">Ảnh đính kèm</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $transaction->attachments->count() }}</span>
        </div>

        <div class="p-4">
            <div class="flex flex-wrap gap-2.5">
                @php $imgIndex = 0; @endphp
                @foreach($transaction->attachments as $att)
                <div class="relative group flex-shrink-0">
                    @if($att->isImage())
                    <button type="button" @click="open({{ $imgIndex++ }})"
                            class="block w-20 h-20 rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600 hover:ring-indigo-400 transition-all focus:outline-none">
                        <img src="{{ $att->url }}" alt="{{ $att->original_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                    </button>
                    @else
                    <a href="{{ $att->url }}" target="_blank"
                       class="flex flex-col items-center justify-center w-20 h-20 rounded-xl ring-1 ring-gray-200 dark:ring-gray-600 hover:ring-indigo-400 transition-all gap-1"
                       style="background:var(--surface-bg)">
                        <i class="ph ph-file-text text-xl" style="color:var(--text-muted)"></i>
                        <span class="text-xs truncate w-16 text-center px-1" style="color:var(--text-muted)">{{ $att->original_name }}</span>
                    </a>
                    @endif

                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none" style="color:var(--text-muted)">
                        {{ $att->human_size }}
                    </span>

                    @if($transaction->isDraft())
                    @can('edit-transactions')
                    <form action="{{ route('transaction-attachments.destroy', $att) }}" method="POST"
                          class="absolute -top-1.5 -right-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
                          onsubmit="return confirm('Xóa ảnh này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-sm transition-colors">
                            <i class="ph ph-x" style="font-size:9px"></i>
                        </button>
                    </form>
                    @endcan
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Lightbox --}}
        <div x-show="lightbox"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm"
             @click.self="lightbox = false">
            <button @click="lightbox = false" class="absolute top-4 right-4 w-9 h-9 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                <i class="ph ph-x text-sm"></i>
            </button>
            <button x-show="images.length > 1" @click="prev()" class="absolute left-4 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                <i class="ph ph-caret-left"></i>
            </button>
            <div class="max-w-4xl max-h-[85vh] mx-16 flex flex-col items-center gap-3">
                <img :src="images[current]?.url" :alt="images[current]?.name" class="max-h-[78vh] max-w-full object-contain rounded-lg shadow-2xl">
                <div class="flex items-center gap-3">
                    <p class="text-white/80 text-sm" x-text="images[current]?.name"></p>
                    <span class="text-white/40 text-xs" x-text="`${current+1} / ${images.length}`"></span>
                </div>
            </div>
            <button x-show="images.length > 1" @click="next()" class="absolute right-4 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                <i class="ph ph-caret-right"></i>
            </button>
            <div x-show="images.length > 1" class="absolute bottom-4 flex gap-1.5">
                <template x-for="(_, i) in images" :key="i">
                    <button @click="current = i" :class="i === current ? 'w-4 bg-white' : 'w-1.5 bg-white/40 hover:bg-white/60'" class="h-1.5 rounded-full transition-all duration-200"></button>
                </template>
            </div>
        </div>
    </div>
    @endif

    {{-- Reject Modal --}}
    @can('reject-transactions')
    @if($requireApproval && $transaction->isPending())
    <div x-data="{ open: false }" @open-reject.window="open = true">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="open = false"></div>
            <div class="modal-panel relative max-w-[min(28rem,98vw)] max-h-[92vh] overflow-y-auto p-5"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(239,68,68,0.10)">
                        <i class="ph ph-x-circle text-sm" style="color:#ef4444"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Từ chối phiếu {{ $transaction->code }}</h3>
                </div>
                <form action="{{ route('transactions.reject', $transaction) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Lý do từ chối <span class="text-red-500 normal-case">*</span></label>
                        <textarea name="reason" rows="3" required placeholder="Nhập lý do từ chối..." class="form-input resize-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl" style="background:#ef4444" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Từ chối</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endcan

</div>
@endsection
