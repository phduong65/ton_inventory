@extends('layouts.admin')

@section('title', 'Phiếu ' . $transaction->code)
@section('page-title', 'Phiếu ' . $transaction->code)
@section('breadcrumb', 'Phiếu NK/XK / ' . $transaction->code)

@php $requireApproval = \App\Models\Setting::get('require_approval', true); @endphp

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Header info --}}
        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white font-mono">{{ $transaction->code }}</h2>
                        @php
                            $typeBadge =
                                $transaction->type === 'IN'
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-orange-100 text-orange-700';
                            $typeLabel = $transaction->type === 'IN' ? 'Phiếu nhập' : 'Phiếu xuất';
                            $statusBadge = match ($transaction->status) {
                                'draft' => 'bg-gray-100 text-gray-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            };
                            $statusLabel = match ($transaction->status) {
                                'draft' => 'Nháp',
                                'pending' => 'Chờ duyệt',
                                'approved' => 'Đã duyệt',
                                'rejected' => 'Từ chối',
                            };
                        @endphp
                        <span
                            class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $typeBadge }}">{{ $typeLabel }}</span>
                        <span
                            class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </div>
                    <p class="text-sm text-gray-500">Ngày: {{ $transaction->date?->format('d/m/Y') }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 flex-wrap justify-end">
                    @can('print-transactions')
                        <a href="{{ route('transactions.print', $transaction) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="bi bi-printer"></i> In
                        </a>
                    @endcan

                    @can('create-transactions')
                    <form action="{{ route('transactions.clone', $transaction) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="bi bi-copy"></i> Nhân bản
                        </button>
                    </form>
                    @endcan

                    @if ($transaction->isDraft())
                        @can('edit-transactions')
                            <a href="{{ route('transactions.edit', $transaction) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="bi bi-pencil"></i> Sửa
                            </a>
                        @endcan
                        @can('create-transactions')
                            <form action="{{ route('transactions.submit', $transaction) }}" method="POST">
                                @csrf
                                @if ($requireApproval)
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                                        <i class="bi bi-send"></i> Gửi chờ duyệt
                                    </button>
                                @else
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                        <i class="bi bi-check-circle"></i> Xác nhận
                                    </button>
                                @endif
                            </form>
                        @endcan

                        @can('delete-transactions')
                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                                onsubmit="return confirm('Xóa phiếu này?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-red-50 text-red-600 hover:bg-red-100 rounded-lg border border-red-200">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </form>
                        @endcan
                    @endif

                    @if ($transaction->isPending())
                        @if(auth()->user()->hasRole('admin') || $transaction->created_by === auth()->id())
                        <form action="{{ route('transactions.cancel', $transaction) }}" method="POST"
                              onsubmit="return confirm('Hủy phiếu và chuyển về nháp?')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-orange-300 text-orange-600 hover:bg-orange-50 rounded-lg">
                                <i class="bi bi-slash-circle"></i> Hủy
                            </button>
                        </form>
                        @endif
                    @endif

                    @if ($requireApproval && $transaction->isPending())
                        @can('reject-transactions')
                            <button x-data @click="$dispatch('open-reject')"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-red-300 text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="bi bi-x-circle"></i> Từ chối
                            </button>
                        @endcan

                        @can('approve-transactions')
                            <form action="{{ route('transactions.approve', $transaction) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                    <i class="bi bi-check-circle"></i> Duyệt
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">{{ $transaction->type === 'IN' ? 'Nhà cung cấp' : 'Điểm nhận' }}</p>
                    <p class="font-medium">{{ $transaction->supplier?->name ?? ($transaction->destination?->name ?? '—') }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Người tạo</p>
                    <p class="font-medium">{{ $transaction->createdBy?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Người duyệt</p>
                    <p class="font-medium">{{ $transaction->approvedBy?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Ghi chú</p>
                    <p class="font-medium">{{ $transaction->note ?: '—' }}</p>
                </div>
            </div>

            @if ($transaction->isRejected() && $transaction->rejected_reason)
                <div
                    class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">
                    <strong>Lý do từ chối:</strong> {{ $transaction->rejected_reason }}
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div
            class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Chi tiết sản phẩm</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Sản phẩm</th>
                        <th class="px-4 py-3 text-left">ĐVT</th>
                        <th class="px-4 py-3 text-right">Số lượng</th>
                        <th class="px-4 py-3 text-right">Quy đổi</th>
                        @if ($transaction->type === 'IN')
                            <th class="px-4 py-3 text-right">Đơn giá</th>
                            <th class="px-4 py-3 text-right">CK%</th>
                            <th class="px-4 py-3 text-right">Tiền CK</th>
                            <th class="px-4 py-3 text-right">VAT%</th>
                            <th class="px-4 py-3 text-right">Tiền VAT</th>
                            <th class="px-4 py-3 text-right">Thành tiền</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($transaction->details as $i => $detail)
                        @php
                            $baseUnitName = $detail->product?->unit?->name ?? '';
                            $detailUnit = $detail->unit?->name ?? $baseUnitName;
                            $hasConversion = $detail->conversion_factor > 1;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $detail->product?->name }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $detailUnit }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format($detail->qty, $detail->qty == floor($detail->qty) ? 0 : 3, ',', '.') }}
                                <span class="text-xs text-gray-400">{{ $detailUnit }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-gray-400">
                                @if ($hasConversion)
                                    {{ number_format($detail->base_qty, $detail->base_qty == floor($detail->base_qty) ? 0 : 3, ',', '.') }}
                                    {{ $baseUnitName }}
                                @else
                                    —
                                @endif
                            </td>
                            @if ($transaction->type === 'IN')
                                @php
                                    $baseAmt = $detail->qty * $detail->price;
                                    $discountAmt = $baseAmt * ($detail->discount / 100);
                                    $vatAmt = ($baseAmt - $discountAmt) * ($detail->vat / 100);
                                @endphp
                                <td class="px-4 py-3 text-right">
                                    {{ number_format($detail->price, 0, ',', '.') }}đ/{{ $detailUnit }}</td>
                                <td class="px-4 py-3 text-right">{{ $detail->discount }}%</td>
                                <td class="px-4 py-3 text-right text-sm" style="color:#f97316">
                                    @if ($discountAmt > 0)
                                        - {{ number_format($discountAmt, 0, ',', '.') }}đ
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">{{ $detail->vat }}%</td>
                                <td class="px-4 py-3 text-right text-sm" style="color:#3b82f6">
                                    @if ($vatAmt > 0)
                                        + {{ number_format($vatAmt, 0, ',', '.') }}đ
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ number_format($detail->amount, 0, ',', '.') }}đ</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                @if ($transaction->type === 'IN')
                    @php
                        $totalDiscountAmt = $transaction->details->sum(
                            fn($d) => $d->qty * $d->price * ($d->discount / 100),
                        );
                        $totalVatAmt = $transaction->details->sum(
                            fn($d) => $d->qty * $d->price * (1 - $d->discount / 100) * ($d->vat / 100),
                        );
                    @endphp
                    <tfoot class="bg-gray-50 dark:bg-gray-700 text-sm">
                        <tr class="border-t border-dashed border-gray-200 dark:border-gray-600">
                            <td colspan="10" class="px-4 py-2 text-right text-xs text-gray-500 dark:text-gray-400">Tổng
                                chiết khấu</td>
                            <td class="px-4 py-2 text-right font-semibold" style="color:#f97316">
                                @if ($totalDiscountAmt > 0)
                                    - {{ number_format($totalDiscountAmt, 0, ',', '.') }}đ
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" class="px-4 py-2 text-right text-xs text-gray-500 dark:text-gray-400">Tổng
                                VAT</td>
                            <td class="px-4 py-2 text-right font-semibold" style="color:#3b82f6">
                                @if ($totalVatAmt > 0)
                                    + {{ number_format($totalVatAmt, 0, ',', '.') }}đ
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr class="border-t-2 border-gray-200 dark:border-gray-600 font-bold">
                            <td colspan="10" class="px-4 py-3 text-right">TỔNG CỘNG:</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                {{ number_format($transaction->details->sum('amount'), 0, ',', '.') }}đ
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Attachments --}}
    @if ($transaction->attachments->count() > 0)
        <div class="lg:col-span-3 mt-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            x-data="{
                lightbox: false,
                current: 0,
                images: {{ $transaction->attachments->filter(fn($a) => $a->isImage())->values()->map(fn($a) => ['url' => $a->url, 'name' => $a->original_name])->toJson() }},
                open(i) { this.current = i;
                    this.lightbox = true; },
                prev() { this.current = (this.current - 1 + this.images.length) % this.images.length; },
                next() { this.current = (this.current + 1) % this.images.length; },
            }" @keydown.escape.window="lightbox = false"
            @keydown.arrow-left.window="lightbox && prev()" @keydown.arrow-right.window="lightbox && next()">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <i class="bi bi-images text-gray-400 text-sm"></i>
                    <span class="font-semibold text-sm text-gray-900 dark:text-white">Ảnh đính kèm</span>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500">
                        {{ $transaction->attachments->count() }}
                    </span>
                </div>
            </div>

            {{-- Thumbnail grid --}}
            <div class="p-4">
                <div class="flex flex-wrap gap-2.5">
                    @php $imgIndex = 0; @endphp
                    @foreach ($transaction->attachments as $att)
                        <div class="relative group flex-shrink-0">
                            @if ($att->isImage())
                                <button type="button" @click="open({{ $imgIndex++ }})"
                                    class="block w-20 h-20 rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600 hover:ring-primary-400 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <img src="{{ $att->url }}" alt="{{ $att->original_name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                </button>
                            @else
                                <a href="{{ $att->url }}" target="_blank"
                                    class="flex flex-col items-center justify-center w-20 h-20 rounded-xl ring-1 ring-gray-200 dark:ring-gray-600 hover:ring-primary-400 transition-all bg-gray-50 dark:bg-gray-700 gap-1">
                                    <i class="bi bi-file-earmark-text text-xl text-gray-400"></i>
                                    <span
                                        class="text-xs text-gray-500 truncate w-16 text-center px-1">{{ $att->original_name }}</span>
                                </a>
                            @endif

                            {{-- Size tooltip --}}
                            <span
                                class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-xs text-gray-400 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                {{ $att->human_size }}
                            </span>

                            {{-- Delete btn --}}
                            @if ($transaction->isDraft())
                                @can('edit-transactions')
                                    <form action="{{ route('transaction-attachments.destroy', $att) }}" method="POST"
                                        class="absolute -top-1.5 -right-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
                                        onsubmit="return confirm('Xóa ảnh này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-sm transition-colors">
                                            <i class="bi bi-x" style="font-size:11px"></i>
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Lightbox --}}
            <div x-show="lightbox" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm"
                @click.self="lightbox = false">

                {{-- Close --}}
                <button @click="lightbox = false"
                    class="absolute top-4 right-4 w-9 h-9 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>

                {{-- Prev --}}
                <button x-show="images.length > 1" @click="prev()"
                    class="absolute left-4 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                    <i class="bi bi-chevron-left"></i>
                </button>

                {{-- Image --}}
                <div class="max-w-4xl max-h-[85vh] mx-16 flex flex-col items-center gap-3">
                    <img :src="images[current]?.url" :alt="images[current]?.name"
                        class="max-h-[78vh] max-w-full object-contain rounded-lg shadow-2xl">
                    <div class="flex items-center gap-3">
                        <p class="text-white/80 text-sm" x-text="images[current]?.name"></p>
                        <span class="text-white/40 text-xs" x-text="`${current+1} / ${images.length}`"></span>
                    </div>
                </div>

                {{-- Next --}}
                <button x-show="images.length > 1" @click="next()"
                    class="absolute right-4 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                    <i class="bi bi-chevron-right"></i>
                </button>

                {{-- Dot indicators --}}
                <div x-show="images.length > 1" class="absolute bottom-4 flex gap-1.5">
                    <template x-for="(_, i) in images" :key="i">
                        <button @click="current = i"
                            :class="i === current ? 'w-4 bg-white' : 'w-1.5 bg-white/40 hover:bg-white/60'"
                            class="h-1.5 rounded-full transition-all duration-200"></button>
                    </template>
                </div>
            </div>
        </div>
    @endif

    {{-- Reject Modal --}}
    @can('reject-transactions')
        @if ($requireApproval && $transaction->isPending())
            <div x-data="{ open: false }" @open-reject.window="open = true">
                <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
                    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-5">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Từ chối phiếu {{ $transaction->code }}
                        </h3>
                        <form action="{{ route('transactions.reject', $transaction) }}" method="POST">
                            @csrf
                            <textarea name="reason" rows="3" required placeholder="Lý do từ chối..."
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white mb-4"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="open = false"
                                    class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Hủy</button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">Từ
                                    chối</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan
@endsection
