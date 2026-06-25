@extends('layouts.admin')

@section('title', 'Nhà cung cấp')
@section('page-title', 'Nhà cung cấp')
@section('breadcrumb', 'Danh mục / Nhà cung cấp')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editSup: {} }">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên, mã, điện thoại..."
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-64">
            <button type="submit" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg">
                <i class="bi bi-search"></i>
            </button>
        </form>
        @can('create-suppliers')
        <button @click="openCreate = true"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <i class="bi bi-plus-lg"></i> Thêm NCC
        </button>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3">Mã</th>
                        <th class="px-4 py-3">Tên nhà cung cấp</th>
                        <th class="px-4 py-3">Điện thoại</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Người liên hệ</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($suppliers as $sup)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $sup->code }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $sup->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $sup->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $sup->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $sup->contact_person ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-suppliers')
                                <button @click="editSup = {{ $sup->toJson() }}; openEdit = true"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 rounded">
                                    <i class="bi bi-pencil text-xs"></i>
                                </button>
                                @endcan
                                @can('delete-suppliers')
                                <form action="{{ route('suppliers.destroy', $sup) }}" method="POST"
                                      onsubmit="return confirm('Xóa NCC {{ $sup->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded">
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Chưa có nhà cung cấp</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $suppliers->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    @can('create-suppliers')
    <div x-show="openCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="openCreate = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Thêm nhà cung cấp</h3>
                <button @click="openCreate = false" class="text-gray-400"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST" class="grid grid-cols-2 gap-4">
                @csrf
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên NCC <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã NCC <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Điện thoại</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã số thuế</label>
                    <input type="text" name="tax_code" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Người liên hệ</label>
                    <input type="text" name="contact_person" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa chỉ</label>
                    <input type="text" name="address" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="col-span-2 flex justify-end gap-2 pt-2">
                    <button type="button" @click="openCreate = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- Edit Modal --}}
    @can('edit-suppliers')
    <div x-show="openEdit" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Sửa nhà cung cấp</h3>
                <button @click="openEdit = false" class="text-gray-400"><i class="bi bi-x-lg"></i></button>
            </div>
            <form :action="`/suppliers/${editSup.id}`" method="POST" class="grid grid-cols-2 gap-4">
                @csrf @method('PUT')
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên NCC <span class="text-red-500">*</span></label>
                    <input type="text" name="name" :value="editSup.name" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã NCC</label>
                    <input type="text" name="code" :value="editSup.code" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Điện thoại</label>
                    <input type="text" name="phone" :value="editSup.phone" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" :value="editSup.email" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã số thuế</label>
                    <input type="text" name="tax_code" :value="editSup.tax_code" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Người liên hệ</label>
                    <input type="text" name="contact_person" :value="editSup.contact_person" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa chỉ</label>
                    <input type="text" name="address" :value="editSup.address" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="col-span-2 flex justify-end gap-2 pt-2">
                    <button type="button" @click="openEdit = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
