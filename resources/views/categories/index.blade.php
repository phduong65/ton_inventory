@extends('layouts.admin')

@section('title', 'Danh mục')
@section('page-title', 'Danh mục sản phẩm')
@section('breadcrumb', 'Danh mục / Danh sách')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editCat: {} }">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5">
            <span class="text-sm font-medium" style="color:var(--text-secondary)">Danh mục sản phẩm</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(99,102,241,0.10);color:#4f46e5">
                {{ $categories->count() }}
            </span>
        </div>
        @can('create-categories')
        <button @click="openCreate = true"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                style="background:#4f46e5"
                onmouseover="this.style.background='#4338ca'"
                onmouseout="this.style.background='#4f46e5'">
            <i class="ph ph-plus text-base"></i>
            Thêm danh mục
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
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Tên danh mục</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Danh mục cha</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Thứ tự</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Sản phẩm</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                @if($cat->parent_id)
                                <span class="text-gray-300 dark:text-gray-600 select-none">└</span>
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background:rgba(99,102,241,0.08)">
                                    <i class="ph ph-tag text-xs" style="color:#6366f1"></i>
                                </div>
                                @else
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background:rgba(99,102,241,0.12)">
                                    <i class="ph ph-folder text-xs" style="color:#4f46e5"></i>
                                </div>
                                @endif
                                <span class="font-medium text-sm" style="color:var(--text-primary)">{{ $cat->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">
                            {{ $cat->parent?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="text-xs tabular-nums font-mono" style="color:var(--text-muted)">{{ $cat->sort }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center min-w-[24px] text-xs font-semibold px-2 py-0.5 rounded-full
                                {{ $cat->products_count > 0 ? 'badge-indigo' : 'badge-gray' }}">
                                {{ $cat->products_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-categories')
                                <button @click="editCat = {{ $cat->toJson() }}; openEdit = true"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--text-muted)"
                                        onmouseover="this.style.background='rgba(99,102,241,0.08)';this.style.color='#4f46e5'"
                                        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                    <i class="ph ph-pencil-simple text-sm"></i>
                                </button>
                                @endcan
                                @can('delete-categories')
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                                      onsubmit="return confirm('Xóa danh mục «{{ $cat->name }}»?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors"
                                            style="color:var(--text-muted)"
                                            onmouseover="this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'"
                                            onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'"
                                            {{ $cat->products_count > 0 ? 'disabled title=Còn sản phẩm, không thể xóa' : '' }}>
                                        <i class="ph ph-trash text-sm"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl mb-3"
                                 style="background:rgba(99,102,241,0.08)">
                                <i class="ph ph-folder-open text-2xl" style="color:#6366f1;opacity:.5"></i>
                            </div>
                            <p class="text-sm" style="color:var(--text-muted)">Chưa có danh mục nào</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Create Modal ─────────────────────────────────────── --}}
    @can('create-categories')
    <div x-show="openCreate"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)"
             @click="openCreate = false"></div>
        <div class="modal-panel relative max-w-[min(28rem,98vw)] max-h-[92vh] overflow-y-auto p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                        <i class="ph ph-folder-plus text-sm" style="color:#4f46e5"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Thêm danh mục</h3>
                </div>
                <button @click="openCreate = false"
                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors"
                        style="color:var(--text-muted)"
                        onmouseover="this.style.background='var(--surface-bg)'"
                        onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">
                        Tên danh mục <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="text" name="name" required autofocus
                           class="form-input" placeholder="VD: Rượu, Bia, Nước ngọt...">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">
                        Danh mục cha
                    </label>
                    <select name="parent_id" class="form-input">
                        <option value="">— Không có (danh mục gốc) —</option>
                        @foreach($roots as $root)
                        <option value="{{ $root->id }}">{{ $root->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">
                        Thứ tự hiển thị
                    </label>
                    <input type="number" name="sort" value="0" min="0" class="form-input">
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="openCreate = false"
                            class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors"
                            style="border-color:var(--surface-border);color:var(--text-secondary)"
                            onmouseover="this.style.background='var(--surface-bg)'"
                            onmouseout="this.style.background='transparent'">
                        Hủy
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                            style="background:#4f46e5"
                            onmouseover="this.style.background='#4338ca'"
                            onmouseout="this.style.background='#4f46e5'">
                        Lưu danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- ── Edit Modal ───────────────────────────────────────── --}}
    @can('edit-categories')
    <div x-show="openEdit"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)"
             @click="openEdit = false"></div>
        <div class="modal-panel relative max-w-[min(28rem,98vw)] max-h-[92vh] overflow-y-auto p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                        <i class="ph ph-pencil-simple text-sm" style="color:#4f46e5"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Sửa danh mục</h3>
                </div>
                <button @click="openEdit = false"
                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors"
                        style="color:var(--text-muted)"
                        onmouseover="this.style.background='var(--surface-bg)'"
                        onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <form :action="`/categories/${editCat.id}`" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">
                        Tên danh mục <span class="text-red-500 normal-case">*</span>
                    </label>
                    <input type="text" name="name" :value="editCat.name" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">
                        Danh mục cha
                    </label>
                    <select name="parent_id" class="form-input">
                        <option value="">— Không có (danh mục gốc) —</option>
                        @foreach($roots as $root)
                        <option :selected="editCat.parent_id == {{ $root->id }}" value="{{ $root->id }}">{{ $root->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">
                        Thứ tự hiển thị
                    </label>
                    <input type="number" name="sort" :value="editCat.sort" min="0" class="form-input">
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="openEdit = false"
                            class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors"
                            style="border-color:var(--surface-border);color:var(--text-secondary)"
                            onmouseover="this.style.background='var(--surface-bg)'"
                            onmouseout="this.style.background='transparent'">
                        Hủy
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                            style="background:#4f46e5"
                            onmouseover="this.style.background='#4338ca'"
                            onmouseout="this.style.background='#4f46e5'">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

</div>
@endsection
