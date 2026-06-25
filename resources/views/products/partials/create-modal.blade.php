<div x-show="openCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="openCreate = false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-xl max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Thêm sản phẩm</h3>
            <button @click="openCreate = false" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('products.store') }}" method="POST"
              x-data="{
                  convRows: [],
                  selectedUnitId: '{{ old('unit_id', '') }}'
              }"
              class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên sản phẩm <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Danh mục</label>
                    <select name="category_id" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">— Chọn —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Đơn vị cơ sở <span class="text-red-500">*</span>
                    </label>
                    {{-- x-model đồng bộ selectedUnitId để hiển thị tên đơn vị trong quy đổi --}}
                    <select name="unit_id" x-model="selectedUnitId" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">— Chọn —</option>
                        @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Giá mặc định</label>
                    <input type="number" name="default_price" value="{{ old('default_price', 0) }}" min="0" step="1000"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ngưỡng tồn tối thiểu
                        <span class="ml-1 text-xs font-normal text-gray-400">(cảnh báo đỏ)</span>
                    </label>
                    <input type="number" name="min_stock" value="{{ old('min_stock') }}" min="0" step="1" placeholder="0 = không cảnh báo"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng thái</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="active">Đang hoạt động</option>
                    <option value="inactive">Ngừng hoạt động</option>
                </select>
            </div>

            {{-- Quy đổi đơn vị --}}
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Quy đổi đơn vị</span>
                    <button type="button"
                            @click="convRows.push({ unit_id: '', factor: 1, note: '' })"
                            class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                        <i class="bi bi-plus-lg" style="font-size:10px"></i> Thêm
                    </button>
                </div>
                <div class="p-3 space-y-2">
                    <template x-if="convRows.length === 0">
                        <p class="text-xs text-gray-400 text-center py-2">Chưa có quy đổi. Thêm để nhập/xuất theo đơn vị lớn hơn (VD: Thùng, Lốc...).</p>
                    </template>
                    <template x-for="(row, i) in convRows" :key="i">
                        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 rounded px-2 py-1.5">
                            <span class="text-xs text-gray-400 w-4 flex-shrink-0 text-center" x-text="i+1"></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 font-medium">1</span>
                            <select :name="`conversions[${i}][unit_id]`" x-model="row.unit_id" required
                                    class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">Chọn đơn vị...</option>
                                @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">=</span>
                            <input type="number" :name="`conversions[${i}][factor]`" x-model="row.factor"
                                   min="0.0001" step="any" required placeholder="Hệ số"
                                   class="w-20 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-right">
                            {{-- Hiển thị tên đơn vị cơ sở thực tế, không phải "đvt cơ sở" cứng --}}
                            <span class="text-xs font-medium flex-shrink-0"
                                  :class="selectedUnitId ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'"
                                  x-text="selectedUnitId && productUnitsMap[selectedUnitId] ? productUnitsMap[selectedUnitId] : 'đvt cơ sở'"></span>
                            <button type="button" @click="convRows.splice(i, 1)"
                                    class="text-red-400 hover:text-red-600 flex-shrink-0 p-0.5">
                                <i class="bi bi-x-lg text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="openCreate = false"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50">
                    Hủy
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-lg">
                    Lưu sản phẩm
                </button>
            </div>
        </form>
    </div>
</div>
