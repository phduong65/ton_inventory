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
                <h3 class="font-semibold text-base mb-1" style="color:var(--text-primary)">Xóa đơn vị tính?</h3>
                <p class="text-sm" style="color:var(--text-muted)">
                    Xóa đơn vị "<span class="font-medium" style="color:var(--text-primary)" x-text="deleteUnit?.name"></span>"?
                    Không thể hoàn tác.
                </p>
            </div>
        </div>
        <template x-if="deleteUnit">
        <form :action="`/units/${deleteUnit.id}`" method="POST" class="flex gap-2">
            @csrf @method('DELETE')
            <button type="button" @click="openDelete = false" class="flex-1 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
            <button type="submit" class="flex-1 py-2 text-sm font-medium text-white rounded-xl" style="background:#ef4444" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Xóa</button>
        </form>
        </template>
    </div>
</div>
