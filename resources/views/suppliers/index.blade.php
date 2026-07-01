@extends('layouts.admin')

@section('title', 'Nhà cung cấp')
@section('page-title', 'Nhà cung cấp')
@section('breadcrumb', 'Danh mục / Nhà cung cấp')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editSup: {} }">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:var(--text-muted)"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Tìm tên, mã, điện thoại..."
                       class="form-input pl-9 w-64 h-9 text-sm">
            </div>
            @if(request('search'))
            <a href="{{ route('suppliers.index') }}"
               class="h-9 px-3 inline-flex items-center text-sm rounded-xl border transition-colors"
               style="border-color:var(--surface-border);color:var(--text-muted)">
                <i class="ph ph-x text-sm"></i>
            </a>
            @endif
        </form>
        @can('create-suppliers')
        <button @click="openCreate = true"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                style="background:#4f46e5"
                onmouseover="this.style.background='#4338ca'"
                onmouseout="this.style.background='#4f46e5'">
            <i class="ph ph-plus text-base"></i> Thêm NCC
        </button>
        @endcan
    </div>

    {{-- ── Table ───────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Mã</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tên nhà cung cấp</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Điện thoại</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Email</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Người liên hệ</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $sup)
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                        <td class="px-5 py-3.5 font-mono text-xs" style="color:var(--text-muted)">{{ $sup->code }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background:rgba(99,102,241,0.08)">
                                    <i class="ph ph-buildings text-xs" style="color:#6366f1"></i>
                                </div>
                                <span class="font-medium text-sm" style="color:var(--text-primary)">{{ $sup->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $sup->phone ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $sup->email ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-secondary)">{{ $sup->contact_person ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-suppliers')
                                <button @click="editSup = {{ $sup->toJson() }}; openEdit = true"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--text-muted)"
                                        onmouseover="this.style.background='rgba(99,102,241,0.08)';this.style.color='#4f46e5'"
                                        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                    <i class="ph ph-pencil-simple text-sm"></i>
                                </button>
                                @endcan
                                @can('delete-suppliers')
                                <form action="{{ route('suppliers.destroy', $sup) }}" method="POST"
                                      onsubmit="return confirm('Xóa NCC «{{ $sup->name }}»?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors"
                                            style="color:var(--text-muted)"
                                            onmouseover="this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'"
                                            onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                        <i class="ph ph-trash text-sm"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl mb-3"
                                 style="background:rgba(99,102,241,0.08)">
                                <i class="ph ph-buildings text-2xl" style="color:#6366f1;opacity:.5"></i>
                            </div>
                            <p class="text-sm" style="color:var(--text-muted)">Chưa có nhà cung cấp</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

    {{-- ── Create Modal ─────────────────────────────────────── --}}
    @can('create-suppliers')
    <div x-show="openCreate"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openCreate = false"></div>
        <div class="modal-panel relative w-full max-w-lg p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                        <i class="ph ph-buildings text-sm" style="color:#4f46e5"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Thêm nhà cung cấp</h3>
                </div>
                <button @click="openCreate = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST" class="grid grid-cols-2 gap-4">
                @csrf
                <div class="col-span-2">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên NCC <span class="text-red-500 normal-case">*</span></label>
                    <input type="text" name="name" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Mã NCC <span class="text-red-500 normal-case">*</span></label>
                    <input type="text" name="code" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Điện thoại</label>
                    <input type="text" name="phone" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Email</label>
                    <input type="email" name="email" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Mã số thuế</label>
                    <input type="text" name="tax_code" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Người liên hệ</label>
                    <input type="text" name="contact_person" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Địa chỉ</label>
                    <input type="text" name="address" class="form-input">
                </div>
                <div class="col-span-2 flex justify-end gap-2 pt-1">
                    <button type="button" @click="openCreate = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- ── Edit Modal ───────────────────────────────────────── --}}
    @can('edit-suppliers')
    <div x-show="openEdit"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openEdit = false"></div>
        <div class="modal-panel relative w-full max-w-lg p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                        <i class="ph ph-pencil-simple text-sm" style="color:#4f46e5"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Sửa nhà cung cấp</h3>
                </div>
                <button @click="openEdit = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <form :action="`/suppliers/${editSup.id}`" method="POST" class="grid grid-cols-2 gap-4">
                @csrf @method('PUT')
                <div class="col-span-2">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên NCC <span class="text-red-500 normal-case">*</span></label>
                    <input type="text" name="name" :value="editSup.name" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Mã NCC</label>
                    <input type="text" name="code" :value="editSup.code" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Điện thoại</label>
                    <input type="text" name="phone" :value="editSup.phone" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Email</label>
                    <input type="email" name="email" :value="editSup.email" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Mã số thuế</label>
                    <input type="text" name="tax_code" :value="editSup.tax_code" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Người liên hệ</label>
                    <input type="text" name="contact_person" :value="editSup.contact_person" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Địa chỉ</label>
                    <input type="text" name="address" :value="editSup.address" class="form-input">
                </div>
                <div class="col-span-2 flex justify-end gap-2 pt-1">
                    <button type="button" @click="openEdit = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
