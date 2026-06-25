@extends('layouts.admin')

@section('title', 'Danh mục')
@section('page-title', 'Danh mục sản phẩm')
@section('breadcrumb', 'Danh mục / Danh sách')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editCat: {} }">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $categories->count() }} danh mục</p>
        @can('create-categories')
        <button @click="openCreate = true"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <i class="bi bi-plus-lg"></i> Thêm danh mục
        </button>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3">Tên danh mục</th>
                        <th class="px-4 py-3">Danh mục cha</th>
                        <th class="px-4 py-3 text-center">Thứ tự</th>
                        <th class="px-4 py-3 text-center">Số sản phẩm</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($categories as $cat)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            @if($cat->parent_id)
                            <span class="text-gray-400 mr-1">└</span>
                            @endif
                            {{ $cat->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $cat->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $cat->sort }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $cat->products_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-categories')
                                <button @click="editCat = {{ $cat->toJson() }}; openEdit = true"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 rounded">
                                    <i class="bi bi-pencil text-xs"></i>
                                </button>
                                @endcan
                                @can('delete-categories')
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                                      onsubmit="return confirm('Xóa danh mục {{ $cat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded"
                                            {{ $cat->products_count > 0 ? 'disabled title=Còn sản phẩm' : '' }}>
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">Chưa có danh mục</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Modal --}}
    @can('create-categories')
    <div x-show="openCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="openCreate = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Thêm danh mục</h3>
                <button @click="openCreate = false" class="text-gray-400"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên danh mục <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Danh mục cha</label>
                    <select name="parent_id" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">— Không có —</option>
                        @foreach($roots as $root)
                        <option value="{{ $root->id }}">{{ $root->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thứ tự</label>
                    <input type="number" name="sort" value="0" min="0" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openCreate = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- Edit Modal --}}
    @can('edit-categories')
    <div x-show="openEdit" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Sửa danh mục</h3>
                <button @click="openEdit = false" class="text-gray-400"><i class="bi bi-x-lg"></i></button>
            </div>
            <form :action="`/categories/${editCat.id}`" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên danh mục <span class="text-red-500">*</span></label>
                    <input type="text" name="name" :value="editCat.name" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Danh mục cha</label>
                    <select name="parent_id" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">— Không có —</option>
                        @foreach($roots as $root)
                        <option :selected="editCat.parent_id == {{ $root->id }}" value="{{ $root->id }}">{{ $root->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thứ tự</label>
                    <input type="number" name="sort" :value="editCat.sort" min="0" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openEdit = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
