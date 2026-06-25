@extends('layouts.admin')

@section('title', 'Tìm kiếm: ' . $q)
@section('page-title', 'Kết quả tìm kiếm')
@section('breadcrumb', 'Tìm kiếm')

@section('content')
<div class="space-y-5">

    {{-- Search bar --}}
    <form method="GET" action="{{ route('search.index') }}" class="flex gap-2">
        <input type="text" name="q" value="{{ $q }}"
               class="flex-1 px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
               placeholder="Tìm phiếu, sản phẩm, nhà cung cấp...">
        <button type="submit"
                class="px-4 py-2.5 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg inline-flex items-center gap-2">
            <i class="bi bi-search"></i> Tìm
        </button>
    </form>

    @if(strlen($q) < 2)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-6 py-12 text-center">
        <i class="ph ph-magnifying-glass text-4xl text-gray-300 block mb-2"></i>
        <p class="text-sm text-gray-400">Nhập ít nhất 2 ký tự để tìm kiếm</p>
    </div>
    @else

    @php
        $totalCount = $results['transactions']->count() + $results['products']->count() + $results['suppliers']->count();
    @endphp

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Tìm thấy <strong class="text-gray-900 dark:text-white">{{ $totalCount }}</strong> kết quả cho "<strong class="text-gray-900 dark:text-white">{{ $q }}</strong>"
    </p>

    {{-- Transactions --}}
    @if($results['transactions']->count())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
            <i class="ph ph-receipt text-gray-500"></i>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Phiếu nhập/xuất</h3>
            <span class="ml-auto text-xs text-gray-400">{{ $results['transactions']->count() }} kết quả</span>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($results['transactions'] as $tx)
            <a href="{{ route('transactions.show', $tx) }}"
               class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <span class="{{ $tx->type === 'IN' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }} inline-flex px-2 py-0.5 rounded text-xs font-medium">
                    {{ $tx->type === 'IN' ? 'Nhập' : 'Xuất' }}
                </span>
                <span class="font-mono text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $tx->code }}</span>
                <span class="text-sm text-gray-500 flex-1 truncate">{{ $tx->note ?? '—' }}</span>
                <span class="text-xs text-gray-400 flex-shrink-0">{{ $tx->date?->format('d/m/Y') }}</span>
                @php $badgeClass = match($tx->status) {
                    'draft' => 'bg-gray-100 text-gray-600', 'pending' => 'bg-yellow-100 text-yellow-700',
                    'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-600'
                }; @endphp
                <span class="{{ $badgeClass }} inline-flex px-2 py-0.5 rounded text-xs font-medium flex-shrink-0">
                    {{ match($tx->status) { 'draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối',default=>$tx->status } }}
                </span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Products --}}
    @if($results['products']->count())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
            <i class="ph ph-package text-gray-500"></i>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Sản phẩm</h3>
            <span class="ml-auto text-xs text-gray-400">{{ $results['products']->count() }} kết quả</span>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($results['products'] as $product)
            <div class="flex items-center gap-4 px-5 py-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $product->sku }} @if($product->barcode) · {{ $product->barcode }} @endif</p>
                </div>
                <span class="text-xs text-gray-500 flex-shrink-0">{{ $product->category?->name }}</span>
                <span class="{{ $product->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} inline-flex px-2 py-0.5 rounded text-xs font-medium flex-shrink-0">
                    {{ $product->status === 'active' ? 'Đang bán' : 'Ngừng' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Suppliers --}}
    @if($results['suppliers']->count())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
            <i class="ph ph-buildings text-gray-500"></i>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Nhà cung cấp</h3>
            <span class="ml-auto text-xs text-gray-400">{{ $results['suppliers']->count() }} kết quả</span>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($results['suppliers'] as $supplier)
            <div class="flex items-center gap-4 px-5 py-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $supplier->name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $supplier->code }}</p>
                </div>
                @if($supplier->phone)
                <span class="text-xs text-gray-500 flex-shrink-0">{{ $supplier->phone }}</span>
                @endif
                @if($supplier->email)
                <span class="text-xs text-gray-500 flex-shrink-0">{{ $supplier->email }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($totalCount === 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-6 py-12 text-center">
        <i class="ph ph-magnifying-glass text-4xl text-gray-300 block mb-2"></i>
        <p class="text-sm text-gray-500">Không tìm thấy kết quả nào cho "<strong>{{ $q }}</strong>"</p>
    </div>
    @endif

    @endif
</div>
@endsection
