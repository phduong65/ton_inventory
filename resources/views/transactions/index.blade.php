@extends('layouts.admin')

@section('title', 'Phiếu nhập / xuất')
@section('page-title', 'Phiếu nhập / xuất kho')
@section('breadcrumb', 'Phiếu NK/XK')

@section('content')

{{-- ── Page Header ──────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-2.5">
        <span class="text-sm font-medium" style="color:var(--text-secondary)">Phiếu giao dịch</span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
              style="background:rgba(99,102,241,0.10);color:#4f46e5">
            {{ $transactions->total() }}
        </span>
    </div>
    @can('create-transactions')
    <div class="flex gap-2">
        <a href="{{ route('transactions.create', ['type' => 'IN']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
           style="background:#3b82f6"
           onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
            <i class="ph ph-arrow-fat-line-down text-base"></i>
            <span class="hidden sm:inline">Tạo</span> Phiếu nhập
        </a>
        <a href="{{ route('transactions.create', ['type' => 'OUT']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
           style="background:#f97316"
           onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
            <i class="ph ph-arrow-fat-line-up text-base"></i>
            <span class="hidden sm:inline">Tạo</span> Phiếu xuất
        </a>
    </div>
    @endcan
</div>

{{-- ── Table Card ───────────────────────────────────────── --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3" style="border-bottom:1px solid var(--surface-border)">
        <select name="type" class="form-input flex-1 min-w-[120px] h-9 text-sm">
            <option value="">Tất cả loại</option>
            <option value="IN"  {{ request('type') === 'IN'  ? 'selected' : '' }}>Nhập kho</option>
            <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Xuất kho</option>
        </select>
        <select name="status" class="form-input flex-1 min-w-[130px] h-9 text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Nháp</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Chờ duyệt</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="form-input flex-1 min-w-[130px] h-9 text-sm">
        <input type="date" name="date_to"   value="{{ request('date_to') }}"
               class="form-input flex-1 min-w-[130px] h-9 text-sm">
        <button type="submit"
                class="h-9 px-4 text-sm font-medium rounded-xl transition-colors"
                style="background:var(--surface-bg);border:1px solid var(--surface-border);color:var(--text-secondary)"
                onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
            <i class="ph ph-magnifying-glass mr-1"></i> Lọc
        </button>
        @if(request()->hasAny(['type','status','date_from','date_to']))
        <a href="{{ route('transactions.index') }}"
           class="h-9 px-3 inline-flex items-center text-sm rounded-xl border transition-colors"
           style="border-color:var(--surface-border);color:var(--text-muted)">
            <i class="ph ph-x text-sm"></i>
        </a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Số phiếu</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Loại</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ngày</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đối tác</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Người tạo</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Trạng thái</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('transactions.show', $tx) }}"
                           class="font-mono text-xs font-semibold hover:underline"
                           style="color:var(--sidebar-accent)">
                            {{ $tx->code }}
                        </a>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($tx->type === 'IN')
                        <span class="badge-blue inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                            <i class="bi bi-download text-[10px]"></i> Nhập
                        </span>
                        @else
                        <span class="badge-orange inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                            <i class="bi bi-upload text-[10px]"></i> Xuất
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5" style="color:var(--text-secondary)">
                        <span class="block text-xs">{{ $tx->date?->format('d/m/Y') }}</span>
                        <span class="block text-[11px] tabular-nums" style="color:var(--text-muted)">{{ $tx->created_at?->format('H:i') }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-primary)">
                        {{ $tx->supplier?->name ?? $tx->destination?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $tx->createdBy?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        @php
                        $badgeClass = match($tx->status) {
                            'draft'    => 'badge-gray',
                            'pending'  => 'badge-yellow',
                            'approved' => 'badge-green',
                            'rejected' => 'badge-red',
                            default    => 'badge-gray',
                        };
                        $label = match($tx->status) {
                            'draft' => 'Nháp', 'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', default => $tx->status,
                        };
                        @endphp
                        <span class="{{ $badgeClass }} inline-flex text-xs font-medium px-2 py-0.5 rounded-full">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('transactions.show', $tx) }}"
                           class="text-xs font-medium hover:underline" style="color:var(--sidebar-accent)">
                            Xem →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <i class="ph ph-receipt text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Chưa có phiếu nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

@endsection
