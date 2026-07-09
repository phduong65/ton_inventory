{{-- Khi đóng modal: set editProduct = null để x-if teardown component → convRows re-initializes khi mở lại --}}
<div x-show="openEdit"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openEdit = false; editProduct = null"></div>
    <div class="modal-panel relative max-w-[min(36rem,98vw)] max-h-[92vh] overflow-y-auto"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        <div class="flex items-center justify-between p-5" style="border-bottom:1px solid var(--surface-border)">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                    <i class="ph ph-pencil-simple text-sm" style="color:#4f46e5"></i>
                </div>
                <h3 class="font-semibold text-base" style="color:var(--text-primary)">Sửa sản phẩm</h3>
            </div>
            <button @click="openEdit = false; editProduct = null" class="w-8 h-8 inline-flex items-center justify-center rounded-lg" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                <i class="ph ph-x text-base"></i>
            </button>
        </div>
        <template x-if="editProduct">
        <form :action="`/products/${editProduct.id}`" method="POST" enctype="multipart/form-data"
              x-data="{
                  convRows: (editProduct.unit_conversions || []).map(c => ({
                      unit_id: String(c.unit_id),
                      factor: c.factor,
                      note: c.note || ''
                  })),
                  selectedUnitId: String(editProduct.unit_id || ''),
                  imagePreview: null,
                  removeImage: false,
                  onImageChange(e) {
                      const file = e.target.files[0];
                      if (!file) { this.imagePreview = null; return; }
                      this.removeImage = false;
                      const reader = new FileReader();
                      reader.onload = ev => { this.imagePreview = ev.target.result; };
                      reader.readAsDataURL(file);
                  }
              }"
              class="p-5 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Hình ảnh</label>
                <div class="flex items-center gap-3">
                    <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center" style="background:var(--surface-bg);border:1px solid var(--surface-border)">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview && editProduct.image_url && !removeImage">
                            <img :src="editProduct.image_url" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview && (!editProduct.image_url || removeImage)">
                            <i class="ph ph-image text-xl" style="color:var(--text-muted)"></i>
                        </template>
                    </div>
                    <div class="flex-1 space-y-1.5">
                        <input type="file" name="image" accept="image/*" @change="onImageChange" class="form-input text-sm">
                        <label class="flex items-center gap-1.5 text-xs" style="color:var(--text-muted)" x-show="editProduct.image_url && !imagePreview">
                            <input type="checkbox" name="remove_image" value="1" x-model="removeImage">
                            Xóa ảnh hiện tại
                        </label>
                    </div>
                </div>
                @error('image')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">SKU <span class="text-red-500 normal-case">*</span></label>
                    <input type="text" name="sku" :value="editProduct.sku" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Barcode</label>
                    <input type="text" name="barcode" :value="editProduct.barcode" class="form-input">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên sản phẩm <span class="text-red-500 normal-case">*</span></label>
                <input type="text" name="name" :value="editProduct.name" required class="form-input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Danh mục</label>
                    <select name="category_id" class="form-input">
                        <option value="">— Chọn —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" :selected="editProduct.category_id == {{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Đơn vị cơ sở <span class="text-red-500 normal-case">*</span></label>
                    <select name="unit_id" x-model="selectedUnitId" required class="form-input">
                        <option value="">— Chọn —</option>
                        @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Giá mặc định</label>
                    <input type="number" name="default_price" :value="editProduct.default_price" min="0" step="1000" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">
                        Ngưỡng tồn tối thiểu
                        <span class="ml-1 text-[10px] normal-case font-normal" style="color:var(--text-muted)">(cảnh báo đỏ)</span>
                    </label>
                    <input type="number" name="min_stock" :value="editProduct.min_stock ?? ''" min="0" step="1" placeholder="0 = không cảnh báo" class="form-input">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Trạng thái</label>
                <select name="status" class="form-input">
                    <option value="active" :selected="editProduct.status === 'active'">Đang hoạt động</option>
                    <option value="inactive" :selected="editProduct.status === 'inactive'">Ngừng hoạt động</option>
                </select>
            </div>

            {{-- Quy đổi đơn vị --}}
            <div class="rounded-xl overflow-hidden border" style="border-color:var(--surface-border)">
                <div class="flex items-center justify-between px-4 py-2.5" style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <span class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Quy đổi đơn vị</span>
                    <button type="button"
                            @click="convRows.push({ unit_id: '', factor: 1, note: '' })"
                            class="text-xs font-medium flex items-center gap-1 transition-colors" style="color:#4f46e5"
                            onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                        <i class="ph ph-plus text-xs"></i> Thêm
                    </button>
                </div>
                <div class="p-3 space-y-2">
                    <template x-if="convRows.length === 0">
                        <p class="text-xs text-center py-2" style="color:var(--text-muted)">Chưa có quy đổi. Thêm để nhập/xuất theo đơn vị lớn hơn (VD: Thùng, Lốc...).</p>
                    </template>
                    <template x-for="(row, i) in convRows" :key="i">
                        <div class="flex items-center gap-2 rounded-lg px-2.5 py-2" style="background:var(--surface-bg)">
                            <span class="text-xs w-4 flex-shrink-0 text-center tabular-nums" style="color:var(--text-muted)" x-text="i+1"></span>
                            <span class="text-xs flex-shrink-0 font-medium" style="color:var(--text-muted)">1</span>
                            <select :name="`conversions[${i}][unit_id]`" x-model="row.unit_id" required class="form-input flex-1 text-sm py-1.5">
                                <option value="">Chọn đơn vị...</option>
                                @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-xs flex-shrink-0" style="color:var(--text-muted)">=</span>
                            <input type="number" :name="`conversions[${i}][factor]`" x-model="row.factor"
                                   min="0.0001" step="any" required placeholder="Hệ số"
                                   class="form-input w-20 text-sm py-1.5 text-right">
                            <span class="text-xs font-medium flex-shrink-0"
                                  :style="selectedUnitId ? 'color:#4f46e5' : 'color:var(--text-muted)'"
                                  x-text="selectedUnitId && productUnitsMap[selectedUnitId] ? productUnitsMap[selectedUnitId] : 'đvt cơ sở'"></span>
                            <button type="button" @click="convRows.splice(i, 1)"
                                    class="w-6 h-6 inline-flex items-center justify-center rounded flex-shrink-0 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <i class="ph ph-x text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" @click="openEdit = false; editProduct = null" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Cập nhật</button>
            </div>
        </form>
        </template>
    </div>
</div>
