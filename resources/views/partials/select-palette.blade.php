{{--
    Select Palette overlay — dùng với selectPalette Alpine component.
    Đặt bên trong thẻ [x-data="selectPalette(...)"] cùng với các keyboard listeners.
    Props:
        $placeholder  — text gợi ý trong ô search (default: 'Tìm kiếm...')
        $emptyText    — text khi không có kết quả (default: 'Không tìm thấy kết quả')
        $countLabel   — label đơn vị sau số kết quả (default: 'kết quả')
--}}
<div x-show="open" class="cp-overlay" style="display:none">
    <div class="cp-backdrop" @click="close()"></div>

    <div class="cp-panel">
        {{-- Search bar --}}
        <div class="cp-searchbar">
            <i class="bi bi-search"></i>
            <input class="sp-search cp-search-input"
                   type="text" autocomplete="off"
                   placeholder="{{ $placeholder ?? 'Tìm kiếm...' }}"
                   x-model="search"
                   @input="filter()">
            <span class="cp-kbd">ESC</span>
        </div>

        {{-- Results --}}
        <div class="sp-list cp-list">
            <template x-if="results.length === 0">
                <div class="cp-empty">
                    <i class="bi bi-search"></i>
                    {{ $emptyText ?? 'Không tìm thấy kết quả' }}
                </div>
            </template>
            <template x-for="(item, idx) in results" :key="String(item.v)">
                <button type="button"
                        class="cp-item"
                        :class="[focusIdx === idx ? 'cp-focused sp-item-focused' : '', !item.v ? 'cp-item-all' : '']"
                        @click="pick(item)"
                        @mouseenter="focusIdx = idx">
                    <div class="cp-item-body">
                        <div class="cp-item-top">
                            <span class="cp-item-name" x-text="item.l"></span>
                            <span class="cp-item-sku" x-show="item.s" x-text="item.s"></span>
                        </div>
                        <div class="cp-item-cat" x-show="item.c" x-text="item.c"></div>
                    </div>
                    <i class="bi bi-check2"
                       x-show="String(item.v) === currentValue"
                       style="color:#6366F1; flex-shrink:0; font-size:14px"></i>
                </button>
            </template>
        </div>

        {{-- Footer --}}
        <div class="cp-footer">
            <span class="cp-count">
                <span x-text="results.length"></span> {{ $countLabel ?? 'kết quả' }}
            </span>
            <div class="cp-hints">
                <span class="cp-hint"><kbd>↑↓</kbd> di chuyển</span>
                <span class="cp-hint"><kbd>↵</kbd> chọn</span>
                <span class="cp-hint"><kbd>ESC</kbd> đóng</span>
            </div>
        </div>
    </div>
</div>
