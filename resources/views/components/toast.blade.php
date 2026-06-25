{{-- Toast Notification Container — fixed bottom-right, z-[200] --}}
<div class="fixed bottom-5 right-5 z-[200] flex flex-col gap-3 pointer-events-none"
    style="width: 350px; max-width: calc(100vw - 2.5rem)">

    <template x-for="item in $store.toast.items" :key="item.id">
        <div x-show="item.visible" x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4" class="toast-card pointer-events-auto"
            :class="`toast-${item.type}`">

            {{-- Icon: solid colored circle --}}
            <div class="toast-icon-wrap" :class="`toast-icon-${item.type}`">
                <i class="bi" style="font-size:17px"
                    :class="{
                        'bi-check2': item.type === 'success',
                        'bi-x-lg': item.type === 'error',
                        'bi-exclamation-lg': item.type === 'warning',
                        'bi-lightbulb-fill': item.type === 'info'
                    }"></i>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <p x-show="item.title" x-text="item.title" class="toast-title"></p>
                <p x-text="item.message" class="toast-message" :class="item.title ? 'mt-0.5' : ''"></p>
            </div>

            {{-- Close --}}
            <button @click="$store.toast.remove(item.id)" class="toast-close-btn" type="button" aria-label="Đóng">
                <i class="bi bi-x"></i>
            </button>

            {{-- Progress bar --}}
            <div class="toast-progress-bar" :style="`animation-duration: ${item.duration}s`"></div>
        </div>
    </template>
</div>
