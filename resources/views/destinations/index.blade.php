@extends('layouts.admin')

@section('title', 'Điểm nhận hàng')
@section('page-title', 'Điểm nhận hàng')
@section('breadcrumb', 'Danh mục / Điểm nhận')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editDest: null, openDelete: false, deleteDest: null }">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5">
            <span class="text-sm font-medium" style="color:var(--text-secondary)">Điểm nhận hàng</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(99,102,241,0.10);color:#4f46e5">{{ $destinations->count() }}</span>
        </div>
        @can('create-destinations')
        <button @click="openCreate = true"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                style="background:#4f46e5"
                onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            <i class="ph ph-plus text-base"></i> Thêm điểm nhận
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
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tên</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Địa chỉ</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ghi chú</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($destinations as $dest)
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background:rgba(59,130,246,0.08)">
                                    <i class="ph ph-warehouse text-xs" style="color:#3b82f6"></i>
                                </div>
                                <span class="font-medium text-sm" style="color:var(--text-primary)">{{ $dest->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $dest->address ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $dest->note ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-destinations')
                                <button @click="editDest = {{ $dest->toJson() }}; openEdit = true"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                        onmouseover="this.style.background='rgba(99,102,241,0.08)';this.style.color='#4f46e5'"
                                        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                    <i class="ph ph-pencil-simple text-sm"></i>
                                </button>
                                @endcan
                                @can('delete-destinations')
                                <button @click="deleteDest = {{ $dest->toJson() }}; openDelete = true"
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
                        <td colspan="4" class="px-5 py-16 text-center">
                            <i class="ph ph-warehouse text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                            <p class="text-sm" style="color:var(--text-muted)">Chưa có điểm nhận hàng</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Create Modal ─────────────────────────────────────── --}}
    @can('create-destinations')
    <div x-show="openCreate"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openCreate = false"></div>
        <div class="modal-panel relative max-w-[min(28rem,98vw)] max-h-[92vh] overflow-y-auto p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.10)">
                        <i class="ph ph-warehouse text-sm" style="color:#3b82f6"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Thêm điểm nhận</h3>
                </div>
                <button @click="openCreate = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <form action="{{ route('destinations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên <span class="text-red-500 normal-case">*</span></label>
                    <input type="text" name="name" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Địa chỉ</label>
                    <input type="text" name="address" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Ghi chú</label>
                    <textarea name="note" rows="2" class="form-input resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="openCreate = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- ── Edit Modal ───────────────────────────────────────── --}}
    @can('edit-destinations')
    <div x-show="openEdit"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openEdit = false"></div>
        <div class="modal-panel relative max-w-[min(28rem,98vw)] max-h-[92vh] overflow-y-auto p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                        <i class="ph ph-pencil-simple text-sm" style="color:#4f46e5"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Sửa điểm nhận</h3>
                </div>
                <button @click="openEdit = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <template x-if="editDest">
                <form :action="`/destinations/${editDest.id}`" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên <span class="text-red-500 normal-case">*</span></label>
                        <input type="text" name="name" :value="editDest.name" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Địa chỉ</label>
                        <input type="text" name="address" :value="editDest.address" class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Ghi chú</label>
                        <textarea name="note" rows="2" class="form-input resize-none" x-text="editDest.note"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="openEdit = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Cập nhật</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
    @endcan

    {{-- ── Delete Modal ─────────────────────────────────────── --}}
    @can('delete-destinations')
    <div x-show="openDelete"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openDelete = false"></div>
        <div class="modal-panel relative max-w-[min(24rem,98vw)] max-h-[92vh] overflow-y-auto p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex flex-col items-center text-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background:rgba(239,68,68,0.10)">
                    <i class="ph ph-trash text-lg" style="color:#ef4444"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-base mb-1" style="color:var(--text-primary)">Xóa điểm nhận?</h3>
                    <p class="text-sm" style="color:var(--text-muted)">Hành động này không thể hoàn tác.</p>
                    <p class="text-sm font-medium mt-1" style="color:var(--text-primary)" x-text="deleteDest?.name"></p>
                </div>
            </div>
            <template x-if="deleteDest">
                <form :action="`/destinations/${deleteDest.id}`" method="POST" class="flex gap-2">
                    @csrf @method('DELETE')
                    <button type="button" @click="openDelete = false" class="flex-1 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-medium text-white rounded-xl" style="background:#ef4444" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Xóa</button>
                </form>
            </template>
        </div>
    </div>
    @endcan
</div>
@endsection
