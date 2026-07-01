@extends('layouts.admin')

@section('title', 'Đơn vị tính')
@section('page-title', 'Đơn vị tính')
@section('breadcrumb', 'Danh mục / Đơn vị tính')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editUnit: null, openDelete: false, deleteUnit: null }">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:var(--text-muted)"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Tìm tên đơn vị..."
                       class="form-input pl-9 w-56 h-9 text-sm">
            </div>
            @if(request('search'))
            <a href="{{ route('units.index') }}"
               class="h-9 px-3 inline-flex items-center rounded-xl border transition-colors"
               style="border-color:var(--surface-border);color:var(--text-muted)">
                <i class="ph ph-x text-sm"></i>
            </a>
            @endif
        </form>
        @can('create-units')
        <button @click="openCreate = true"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                style="background:#4f46e5"
                onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            <i class="ph ph-plus text-base"></i> Thêm đơn vị
        </button>
        @endcan
    </div>

    {{-- ── Table ───────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Mã</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tên đơn vị</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SP dùng (base)</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">SP có quy đổi</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                        <td class="px-5 py-3.5 font-mono text-xs font-semibold tracking-wider" style="color:var(--text-muted)">
                            {{ $unit->code }}
                        </td>
                        <td class="px-5 py-3.5 font-medium text-sm" style="color:var(--text-primary)">
                            {{ $unit->name }}
                        </td>
                        <td class="px-5 py-3.5 text-right text-sm tabular-nums" style="color:var(--text-muted)">
                            {{ $unit->products_count }}
                        </td>
                        <td class="px-5 py-3.5 text-right text-sm tabular-nums" style="color:var(--text-muted)">
                            {{ $unit->unit_conversions_count }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-units')
                                <button @click="editUnit = {{ $unit->toJson() }}; openEdit = true"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                        onmouseover="this.style.background='rgba(99,102,241,0.08)';this.style.color='#4f46e5'"
                                        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                    <i class="ph ph-pencil-simple text-sm"></i>
                                </button>
                                @endcan
                                @can('delete-units')
                                <button @click="deleteUnit = {{ $unit->toJson() }}; openDelete = true"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                        onmouseover="this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'"
                                        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                    <i class="ph ph-trash text-sm"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <i class="ph ph-ruler text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                            <p class="text-sm" style="color:var(--text-muted)">Chưa có đơn vị tính nào</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($units->hasPages())
        <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
            {{ $units->links() }}
        </div>
        @endif
    </div>

    @include('units.partials.create-modal')
    @include('units.partials.edit-modal')
    @include('units.partials.delete-modal')
</div>
@endsection
