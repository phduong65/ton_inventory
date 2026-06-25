<div x-show="openDelete" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="openDelete = false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-5">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Xóa đơn vị tính</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Xóa đơn vị "<span class="font-medium text-gray-700 dark:text-gray-200" x-text="deleteUnit?.name"></span>"?
            Không thể hoàn tác.
        </p>
        <template x-if="deleteUnit">
        <form :action="`/units/${deleteUnit.id}`" method="POST">
            @csrf @method('DELETE')
            <div class="flex justify-end gap-2">
                <button type="button" @click="openDelete = false"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Hủy
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    Xóa
                </button>
            </div>
        </form>
        </template>
    </div>
</div>
