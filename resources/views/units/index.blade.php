@extends('layouts.admin')

@section('title', 'Đơn vị tính')
@section('page-title', 'Đơn vị tính')
@section('breadcrumb', 'Danh mục / Đơn vị tính')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editUnit: null, openDelete: false, deleteUnit: null }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $units->total() }} đơn vị tính</p>
        <div class="flex gap-2">
            @can('create-units')
            <button @click="openCreate = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="bi bi-plus-lg"></i> Thêm đơn vị
            </button>
            @endcan
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Filter --}}
        <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên đơn vị..."
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-56">
            <button type="submit" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                <i class="bi bi-search mr-1"></i> Lọc
            </button>
            @if(request('search'))
            <a href="{{ route('units.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 rounded-lg">
                <i class="bi bi-x-circle mr-1"></i> Xóa lọc
            </a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Mã</th>
                        <th class="px-4 py-3">Tên đơn vị</th>
                        <th class="px-4 py-3 text-right">Sp dùng (base)</th>
                        <th class="px-4 py-3 text-right">Sp có quy đổi</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($units as $unit)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-500 dark:text-gray-400 tracking-wider">
                            {{ $unit->code }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            {{ $unit->name }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                            {{ $unit->products_count }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                            {{ $unit->unit_conversions_count }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('edit-units')
                                <button @click="editUnit = {{ $unit->toJson() }}; openEdit = true"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded"
                                        title="Sửa">
                                    <i class="bi bi-pencil text-xs"></i>
                                </button>
                                @endcan
                                @can('delete-units')
                                <button @click="deleteUnit = {{ $unit->toJson() }}; openDelete = true"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                        title="Xóa">
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            <i class="ph ph-ruler text-4xl block mb-2"></i>
                            Chưa có đơn vị tính nào
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($units->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $units->links() }}
        </div>
        @endif
    </div>

    @include('units.partials.create-modal')
    @include('units.partials.edit-modal')
    @include('units.partials.delete-modal')
</div>
@endsection
