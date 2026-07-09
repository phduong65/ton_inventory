@extends('layouts.admin')

@section('title', 'Tìm kiếm: ' . $q)
@section('page-title', 'Kết quả tìm kiếm')
@section('breadcrumb', 'Tìm kiếm')

@section('content')
<div class="space-y-5">

    {{-- Search bar --}}
    <form method="GET" action="{{ route('search.index') }}" class="flex gap-2">
        <div class="relative flex-1">
            <input type="text" name="q" value="{{ $q }}"
                   class="form-input pl-10 w-full h-10 text-sm"
                   placeholder="Tìm phiếu, sản phẩm, nhà cung cấp..." autofocus>
        </div>
        <button type="submit"
                class="px-5 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            Tìm
        </button>
    </form>

    @if(strlen($q) < 2)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 px-6 py-16 text-center"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <i class="ph ph-magnifying-glass text-4xl block mb-2" style="color:var(--text-muted);opacity:.3"></i>
        <p class="text-sm" style="color:var(--text-muted)">Nhập ít nhất 2 ký tự để tìm kiếm</p>
    </div>
    @else

    @php
    $totalCount = $results['transactions']->count() + $results['products']->count() + $results['suppliers']->count();
    @endphp

    <p class="text-sm" style="color:var(--text-muted)">
        Tìm thấy <strong style="color:var(--text-primary)">{{ $totalCount }}</strong> kết quả cho "<strong style="color:var(--text-primary)">{{ $q }}</strong>"
    </p>

    {{-- Transactions --}}
    @if($results['transactions']->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--surface-border)">
            <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background:rgba(99,102,241,0.08)">
                <i class="ph ph-receipt text-xs" style="color:#6366f1"></i>
            </div>
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Phiếu nhập/xuất</h3>
            <span class="ml-auto text-xs px-2 py-0.5 rounded-full font-semibold" style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $results['transactions']->count() }}</span>
        </div>
        <div>
            @foreach($results['transactions'] as $tx)
            <a href="{{ route('transactions.show', $tx) }}"
               class="flex items-center gap-4 px-5 py-3 border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors first:border-t-0">
                @if($tx->type === 'IN')
                <span class="badge-blue inline-flex px-2 py-0.5 rounded-full text-xs font-medium">Nhập</span>
                @else
                <span class="badge-orange inline-flex px-2 py-0.5 rounded-full text-xs font-medium">Xuất</span>
                @endif
                <span class="font-mono text-sm font-semibold" style="color:var(--sidebar-accent)">{{ $tx->code }}</span>
                <span class="text-sm flex-1 truncate" style="color:var(--text-muted)">{{ $tx->note ?? '—' }}</span>
                <span class="text-xs flex-shrink-0" style="color:var(--text-muted)">{{ $tx->date?->format('d/m/Y') }}</span>
                @php
                $badgeClass = match($tx->status) {
                    'draft' => 'badge-gray', 'pending' => 'badge-yellow',
                    'approved' => 'badge-green', 'rejected' => 'badge-red', default => 'badge-gray'
                };
                $label = match($tx->status) {
                    'draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối',default=>$tx->status
                };
                @endphp
                <span class="{{ $badgeClass }} inline-flex px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0">{{ $label }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Products --}}
    @if($results['products']->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--surface-border)">
            <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background:rgba(99,102,241,0.08)">
                <i class="ph ph-package text-xs" style="color:#6366f1"></i>
            </div>
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Sản phẩm</h3>
            <span class="ml-auto text-xs px-2 py-0.5 rounded-full font-semibold" style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $results['products']->count() }}</span>
        </div>
        <div>
            @foreach($results['products'] as $product)
            <div class="flex items-center gap-4 px-5 py-3 border-t border-gray-50 dark:border-gray-700/60 first:border-t-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $product->name }}</p>
                    <p class="text-xs font-mono" style="color:var(--text-muted)">{{ $product->sku }} @if($product->barcode) · {{ $product->barcode }} @endif</p>
                </div>
                <span class="text-xs flex-shrink-0" style="color:var(--text-muted)">{{ $product->category?->name }}</span>
                @if($product->status === 'active')
                <span class="badge-green inline-flex px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0">Đang bán</span>
                @else
                <span class="badge-gray inline-flex px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0">Ngừng</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Suppliers --}}
    @if($results['suppliers']->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--surface-border)">
            <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background:rgba(99,102,241,0.08)">
                <i class="ph ph-buildings text-xs" style="color:#6366f1"></i>
            </div>
            <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Nhà cung cấp</h3>
            <span class="ml-auto text-xs px-2 py-0.5 rounded-full font-semibold" style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $results['suppliers']->count() }}</span>
        </div>
        <div>
            @foreach($results['suppliers'] as $supplier)
            <div class="flex items-center gap-4 px-5 py-3 border-t border-gray-50 dark:border-gray-700/60 first:border-t-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $supplier->name }}</p>
                    <p class="text-xs font-mono" style="color:var(--text-muted)">{{ $supplier->code }}</p>
                </div>
                @if($supplier->phone)
                <span class="text-xs flex-shrink-0" style="color:var(--text-muted)">{{ $supplier->phone }}</span>
                @endif
                @if($supplier->email)
                <span class="text-xs flex-shrink-0" style="color:var(--text-muted)">{{ $supplier->email }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($totalCount === 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 px-6 py-16 text-center"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <i class="ph ph-magnifying-glass text-4xl block mb-2" style="color:var(--text-muted);opacity:.3"></i>
        <p class="text-sm" style="color:var(--text-muted)">Không tìm thấy kết quả nào cho "<strong>{{ $q }}</strong>"</p>
    </div>
    @endif

    @endif
</div>
@endsection
