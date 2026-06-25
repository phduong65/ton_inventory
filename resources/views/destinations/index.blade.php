@extends('layouts.admin')

@section('title', 'Kho nhận hàng')
@section('page-title', 'Kho nhận hàng')
@section('breadcrumb', 'Danh mục / Kho nhận hàng')

@section('content')
<div x-data="{
    openCreate: false,
    openEdit: false,
    openDelete: false,
    editDest: {},
    deleteId: null,
    deleteName: '',
    openEditModal(dest) {
        this.editDest = dest;
        this.openEdit = true;
    },
    openDeleteModal(id, name) {
        this.deleteId = id;
        this.deleteName = name;
        this.openDelete = true;
    }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Tìm mã, tên, người quản lý..."
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-64">
            <button type="submit"
                    class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                <i class="bi bi-search"></i>
            </button>
            @if(request('search'))
            <a href="{{ route('destinations.index') }}"
               class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <i class="bi bi-x-circle"></i>
            </a>
            @endif
        </form>
        @can('manage-destinations')
        <button @click="openCreate = true"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="bi bi-plus-lg"></i> Thêm kho
        </button>
        @endcan
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3">Mã kho</th>
                        <th class="px-4 py-3">Tên kho</th>
                        <th class="px-4 py-3">Người quản lý</th>
                        <th class="px-4 py-3">Điện thoại</th>
                        <th class="px-4 py-3">Địa chỉ</th>
                        <th class="px-4 py-3 text-right">Phiếu xuất</th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($destinations as $dest)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                            {{ $dest->code ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-building text-xs text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $dest->name }}</p>
                                    @if($dest->note)
                                    <p class="text-xs text-gray-400 truncate max-w-[180px]">{{ $dest->note }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $dest->manager ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $dest->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate" title="{{ $dest->address }}">
                            {{ $dest->address ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($dest->transactions_count > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ number_format($dest->transactions_count) }}
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('manage-destinations')
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEditModal({{ $dest->toJson() }})"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded transition-colors"
                                        title="Sửa">
                                    <i class="bi bi-pencil text-xs"></i>
                                </button>
                                <button @click="openDeleteModal({{ $dest->id }}, '{{ addslashes($dest->name) }}')"
                                        class="p-1.5 rounded transition-colors {{ $dest->transactions_count > 0 ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'text-gray-400 hover:text-red-600 dark:hover:text-red-400' }}"
                                        {{ $dest->transactions_count > 0 ? 'disabled title="Kho đã có phiếu xuất, không thể xóa"' : 'title="Xóa"' }}>
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="ph-warehouse text-4xl block mb-2 opacity-40"></i>
                            Chưa có kho nhận hàng nào
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($destinations->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $destinations->links() }}
        </div>
        @endif
    </div>

    @can('manage-destinations')

    {{-- Create Modal --}}
    <div x-show="openCreate" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="openCreate = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Thêm kho nhận hàng</h3>
                <button @click="openCreate = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('destinations.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã kho</label>
                        <input type="text" name="code" placeholder="KHO43"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 uppercase"
                               style="text-transform:uppercase"
                               value="{{ old('code') }}">
                        @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tên kho <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Kho 43"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               value="{{ old('name') }}">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Người quản lý</label>
                        <input type="text" name="manager" placeholder="Nguyễn Văn A"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               value="{{ old('manager') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Điện thoại</label>
                        <input type="text" name="phone" placeholder="0901 234 567"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               value="{{ old('phone') }}">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa chỉ</label>
                        <input type="text" name="address" placeholder="Tầng 1, Khu A..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               value="{{ old('address') }}">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ghi chú</label>
                        <textarea name="note" rows="2" placeholder="Thông tin bổ sung..."
                                  class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('note') }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="openCreate = false"
                            class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        Hủy
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="openEdit" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Sửa kho nhận hàng</h3>
                <button @click="openEdit = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form :action="`/destinations/${editDest.id}`" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã kho</label>
                        <input type="text" name="code" :value="editDest.code"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 uppercase"
                               style="text-transform:uppercase">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tên kho <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" :value="editDest.name" required
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Người quản lý</label>
                        <input type="text" name="manager" :value="editDest.manager"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Điện thoại</label>
                        <input type="text" name="phone" :value="editDest.phone"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa chỉ</label>
                        <input type="text" name="address" :value="editDest.address"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ghi chú</label>
                        <textarea name="note" rows="2" x-text="editDest.note"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="openEdit = false"
                            class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        Hủy
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div x-show="openDelete" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="openDelete = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-5">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Xóa kho nhận hàng</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Bạn có chắc muốn xóa <strong x-text="deleteName" class="text-gray-700 dark:text-gray-300"></strong>?
                        Hành động này không thể hoàn tác.
                    </p>
                </div>
            </div>
            <form :action="`/destinations/${deleteId}`" method="POST" class="flex justify-end gap-2">
                @csrf @method('DELETE')
                <button type="button" @click="openDelete = false"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    Hủy
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Xóa
                </button>
            </form>
        </div>
    </div>

    @endcan
</div>
@endsection
