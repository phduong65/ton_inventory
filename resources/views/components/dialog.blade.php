{{-- Confirm / Alert Dialog — fixed overlay, z-[300] --}}
<div x-show="$store.dialog.visible" @keydown.escape.window="$store.dialog.cancel()"
    class="fixed inset-0 z-[300] flex items-center justify-center p-1"
    x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150 ease-in"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display:none">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px)"
        @click="$store.dialog.cancel()"></div>

    {{-- Card --}}
    <div class="dialog-card" :class="`dialog-card-${$store.dialog.variant}`"
        x-transition:enter="transition duration-250 ease-out" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.stop>

        {{-- Close X --}}
        <button @click="$store.dialog.cancel()" class="dialog-close-btn" type="button" aria-label="Đóng">
            <i class="bi bi-x" style="font-size:18px"></i>
        </button>

        {{-- Inner padding wrapper (below the accent bar) --}}
        <div class="dialog-inner">

            {{-- Icon with rings --}}
            <div class="flex justify-center">
                <div class="dialog-icon-circle" :class="`dialog-icon-${$store.dialog.variant}`">
                    <i class="bi" style="font-size:30px"
                        :class="{
                            'bi-info-circle-fill': $store.dialog.variant === 'info',
                            'bi-check-circle-fill': $store.dialog.variant === 'success',
                            'bi-exclamation-triangle-fill': $store.dialog.variant === 'warning',
                            'bi-exclamation-octagon-fill': $store.dialog.variant === 'danger'
                        }"></i>
                </div>
            </div>

            {{-- Title --}}
            <h3 class="dialog-title" x-text="$store.dialog.title"></h3>

            {{-- Message --}}
            <p class="dialog-message" x-text="$store.dialog.message" x-show="$store.dialog.message"></p>

            {{-- Actions --}}
            <div class="dialog-actions">
                <button @click="$store.dialog.cancel()" class="dialog-btn dialog-btn-cancel" type="button"
                    x-text="$store.dialog.cancelText"></button>

                <button @click="$store.dialog.confirm()" class="dialog-btn dialog-btn-confirm"
                    :class="`dialog-btn-${$store.dialog.variant}`" type="button"
                    x-text="$store.dialog.confirmText"></button>
            </div>

        </div>
    </div>
</div>
