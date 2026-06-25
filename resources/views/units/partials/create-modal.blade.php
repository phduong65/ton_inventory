<div x-show="openCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="openCreate = false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm">
        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Thêm đơn vị tính</h3>
            <button @click="openCreate = false" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('units.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Mã đơn vị <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}"
                       placeholder="VD: CHAI, LON, THUNG, KG..."
                       required autofocus maxlength="20"
                       style="text-transform:uppercase"
                       oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')"
                       class="w-full px-3 py-2 text-sm font-mono border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-400">Chữ in hoa, số, dấu gạch dưới. VD: LON, KG, M3</p>
                @error('code')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tên đơn vị <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="VD: Chai, Lon, Thùng, Kg..."
                       required maxlength="50"
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="openCreate = false"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    Hủy
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-lg">
                    Lưu
                </button>
            </div>
        </form>
    </div>
</div>
