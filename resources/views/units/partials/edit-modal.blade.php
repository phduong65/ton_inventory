<div x-show="openEdit"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openEdit = false"></div>
    <div class="modal-panel relative w-full max-w-sm p-6"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                    <i class="ph ph-pencil-simple text-sm" style="color:#4f46e5"></i>
                </div>
                <h3 class="font-semibold text-base" style="color:var(--text-primary)">Sửa đơn vị tính</h3>
            </div>
            <button @click="openEdit = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                <i class="ph ph-x text-base"></i>
            </button>
        </div>
        <template x-if="editUnit">
        <form :action="`/units/${editUnit.id}`" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Mã đơn vị <span class="text-red-500 normal-case">*</span></label>
                <input type="text" name="code" :value="editUnit.code" required maxlength="20"
                       style="text-transform:uppercase"
                       oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')"
                       class="form-input font-mono">
                <p class="mt-1 text-xs" style="color:var(--text-muted)">Chữ in hoa, số, dấu gạch dưới.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên đơn vị <span class="text-red-500 normal-case">*</span></label>
                <input type="text" name="name" :value="editUnit.name" required maxlength="50" class="form-input">
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" @click="openEdit = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Cập nhật</button>
            </div>
        </form>
        </template>
    </div>
</div>
