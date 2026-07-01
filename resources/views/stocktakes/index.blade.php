@extends('layouts.admin')

@section('title', 'Kiểm kê kho')
@section('page-title', 'Kiểm kê kho')
@section('breadcrumb', 'Kiểm kê / Danh sách')

@section('content')

{{-- ── Page Header ──────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <select name="destination_id" class="form-input h-9 text-sm w-40" onchange="this.form.submit()">
            <option value="">Tất cả kho</option>
            <option value="0" {{ request('destination_id') === '0' ? 'selected' : '' }}>Kho Tổng (40)</option>
            @foreach($destinations as $dest)
            <option value="{{ $dest->id }}" {{ request('destination_id') == $dest->id ? 'selected' : '' }}>{{ $dest->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-input h-9 text-sm w-36" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Nháp</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Chờ duyệt</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
        </select>
        @if(request('destination_id') || request('status'))
        <a href="{{ route('stocktakes.index') }}"
           class="h-9 px-3 inline-flex items-center rounded-xl border text-sm transition-colors"
           style="border-color:var(--surface-border);color:var(--text-muted)">
            <i class="ph ph-x mr-1 text-sm"></i> Xóa lọc
        </a>
        @endif
        <span class="text-xs ml-1" style="color:var(--text-muted)">{{ $stocktakes->total() }} phiếu</span>
    </form>

    @can('create-stocktakes')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('stocktakes.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
           style="background:#7c3aed"
           onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#7c3aed'">
            <i class="ph ph-plus text-base"></i> Kho Tổng
        </a>
        @foreach($destinations as $dest)
        <a href="{{ route('stocktakes.create', ['destination_id' => $dest->id]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
           style="background:#3b82f6"
           onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
            <i class="ph ph-plus text-base"></i> {{ $dest->name }}
        </a>
        @endforeach
    </div>
    @endcan
</div>

{{-- ── Table Card ───────────────────────────────────────── --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Mã phiếu</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Kho</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ngày tạo</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Người tạo</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Phạm vi</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Số SP</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Trạng thái</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ghi chú</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocktakes as $st)
                @php
                $badgeClass = match($st->status) {
                    'draft'    => 'badge-gray',
                    'pending'  => 'badge-yellow',
                    'approved' => 'badge-green',
                    default    => 'badge-gray',
                };
                $label = match($st->status) {
                    'draft' => 'Nháp', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', default => $st->status,
                };
                @endphp
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('stocktakes.show', $st) }}"
                           class="font-mono text-xs font-semibold hover:underline" style="color:var(--sidebar-accent)">
                            {{ $st->code }}
                        </a>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($st->destination)
                        <span class="badge-blue inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                            <i class="ph ph-warehouse text-xs"></i> {{ $st->destination->name }}
                        </span>
                        @else
                        <span class="badge-purple inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full">
                            <i class="ph ph-warehouse text-xs"></i> Kho Tổng
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-secondary)">
                        {{ $st->created_at->format('d/m/Y') }}
                        <span class="text-[10px]" style="color:var(--text-muted)">{{ $st->created_at->format('H:i') }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-secondary)">{{ $st->createdBy?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        @if($st->destination)
                        <span class="text-xs" style="color:var(--text-muted)">—</span>
                        @elseif($st->category)
                        <span class="badge-purple inline-flex px-2 py-0.5 rounded-full text-xs font-medium">{{ $st->category->name }}</span>
                        @else
                        <span class="badge-gray inline-flex px-2 py-0.5 rounded-full text-xs font-medium">Tổng kho</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center text-xs tabular-nums" style="color:var(--text-muted)">
                        {{ $st->details_count }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="{{ $badgeClass }} inline-flex px-2 py-0.5 rounded-full text-xs font-medium">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-xs max-w-[160px] truncate" style="color:var(--text-muted)">
                        {{ $st->note ?? '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('stocktakes.show', $st) }}"
                           class="text-xs font-medium hover:underline" style="color:var(--sidebar-accent)">
                            Xem →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-16 text-center">
                        <i class="ph ph-clipboard-text text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Chưa có phiếu kiểm kê nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stocktakes->hasPages())
    <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
        {{ $stocktakes->links() }}
    </div>
    @endif
</div>

@endsection
