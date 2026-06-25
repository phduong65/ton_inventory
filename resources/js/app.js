import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

// ─── Toast Notification Store ──────────────────────────────
Alpine.store('toast', {
    items: [],

    add(type, title, message, duration = 4000) {
        const id = Date.now() + Math.random();
        const item = { id, type, title: title || null, message, duration: duration / 1000, visible: true };
        this.items.unshift(item);
        if (this.items.length > 5) this.items.pop();
        if (duration > 0) {
            item._timer = setTimeout(() => this.remove(id), duration);
        }
    },

    remove(id) {
        const item = this.items.find(n => n.id === id);
        if (!item) return;
        if (item._timer) clearTimeout(item._timer);
        item.visible = false;
        setTimeout(() => { this.items = this.items.filter(n => n.id !== id); }, 300);
    }
});

// ─── Confirm Dialog Store ──────────────────────────────────
Alpine.store('dialog', {
    visible: false,
    variant: 'info',
    title: '',
    message: '',
    confirmText: 'Xác nhận',
    cancelText: 'Hủy',
    _resolve: null,

    open({ variant = 'info', title = '', message = '', confirmText = 'Xác nhận', cancelText = 'Hủy' } = {}) {
        this.variant   = variant;
        this.title     = title;
        this.message   = message;
        this.confirmText = confirmText;
        this.cancelText  = cancelText;
        this.visible   = true;
        return new Promise(resolve => { this._resolve = resolve; });
    },

    confirm() {
        this.visible = false;
        if (this._resolve) { this._resolve(true); this._resolve = null; }
    },

    cancel() {
        this.visible = false;
        if (this._resolve) { this._resolve(false); this._resolve = null; }
    }
});

// ─── Select Palette component ──────────────────────────────
// Usage: x-data="selectPalette({ value: '{{ request('id') }}', items: [...] })"
// items shape: [{ v: '', l: 'Tất cả' }, { v: 1, l: 'Tên', s: 'SKU' }, ...]
Alpine.data('selectPalette', (config) => ({
    open: false,
    search: '',
    allItems: config.items || [],
    results: config.items || [],
    currentValue: String(config.value ?? ''),
    focusIdx: -1,

    get currentLabel() {
        const found = this.allItems.find(i => String(i.v) === this.currentValue);
        return found ? found.l : '';
    },

    openPalette() {
        this.search = '';
        this.results = this.allItems;
        this.focusIdx = -1;
        this.open = true;
        this.$nextTick(() => {
            this.$root.querySelector('.sp-search')?.focus();
            this.$root.querySelector('.sp-list')?.scrollTo(0, 0);
        });
    },

    close() { this.open = false; },

    filter() {
        const q = this.search.toLowerCase().trim();
        if (!q) { this.results = this.allItems; this.focusIdx = -1; return; }
        this.results = this.allItems.filter(i =>
            i.v === '' ||
            i.l.toLowerCase().includes(q) ||
            (i.s || '').toLowerCase().includes(q)
        );
        // Skip the "all" option when auto-focusing after search
        const firstReal = this.results.findIndex(i => i.v !== '');
        this.focusIdx = firstReal !== -1 ? firstReal : (this.results.length ? 0 : -1);
    },

    pick(item) {
        this.currentValue = String(item.v);
        this.close();
    },

    moveDown() {
        if (this.focusIdx < this.results.length - 1) {
            this.focusIdx++;
            this.$nextTick(() => this.$root.querySelector('.sp-item-focused')?.scrollIntoView({ block: 'nearest' }));
        }
    },

    moveUp() {
        if (this.focusIdx > 0) {
            this.focusIdx--;
            this.$nextTick(() => this.$root.querySelector('.sp-item-focused')?.scrollIntoView({ block: 'nearest' }));
        }
    },

    confirm() {
        const item = this.results[this.focusIdx] ?? (this.results.length === 1 ? this.results[0] : null);
        if (item) this.pick(item);
    },
}));

// ─── Date Picker component ────────────────────────────────
// Usage: <x-date-picker name="date_from" :value="$from" />
Alpine.data('datePicker', (cfg = {}) => ({
    open: false,
    view: 'day',   // 'day' | 'month' | 'year'
    vy: 0, vm: 0,  // view year / month
    sel: null,     // selected Date (local midnight)
    maxDate: null,

    MONTHS: ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6',
             'Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
    DAYS: ['T2','T3','T4','T5','T6','T7','CN'],

    init() {
        const now = new Date();
        this.vy = now.getFullYear();
        this.vm = now.getMonth();
        if (cfg.maxDate === 'today') {
            const t = new Date(); t.setHours(23,59,59,999);
            this.maxDate = t;
        } else if (cfg.maxDate) {
            this.maxDate = new Date(cfg.maxDate);
        }
        if (cfg.value) {
            const parts = cfg.value.split('-').map(Number);
            if (parts.length === 3 && parts[0]) {
                this.sel = new Date(parts[0], parts[1] - 1, parts[2]);
                this.vy = parts[0]; this.vm = parts[1] - 1;
            }
        }
    },

    get hiddenValue() {
        if (!this.sel) return '';
        return [this.sel.getFullYear(),
                String(this.sel.getMonth()+1).padStart(2,'0'),
                String(this.sel.getDate()).padStart(2,'0')].join('-');
    },

    get displayValue() {
        if (!this.sel) return '';
        return [String(this.sel.getDate()).padStart(2,'0'),
                String(this.sel.getMonth()+1).padStart(2,'0'),
                this.sel.getFullYear()].join('/');
    },

    get headerLabel() {
        if (this.view === 'day') return this.MONTHS[this.vm] + ' ' + this.vy;
        if (this.view === 'month') return String(this.vy);
        const d = Math.floor(this.vy / 10) * 10;
        return d + ' – ' + (d + 9);
    },

    get calendarDays() {
        const today = new Date(); today.setHours(0,0,0,0);
        const first = new Date(this.vy, this.vm, 1);
        const startDow = (first.getDay() + 6) % 7; // Mon=0
        const dim = new Date(this.vy, this.vm + 1, 0).getDate();
        const days = [];
        const selTs = this.sel ? new Date(this.sel.getFullYear(), this.sel.getMonth(), this.sel.getDate()).getTime() : null;

        for (let i = startDow - 1; i >= 0; i--) {
            const dt = new Date(this.vy, this.vm, -i);
            days.push({ d: dt.getDate(), curr: false, today: false, selected: false, disabled: this._dis(dt), ts: dt.getTime() });
        }
        for (let i = 1; i <= dim; i++) {
            const dt = new Date(this.vy, this.vm, i);
            days.push({ d: i, curr: true, today: dt.getTime() === today.getTime(), selected: selTs === dt.getTime(), disabled: this._dis(dt), ts: dt.getTime() });
        }
        const rem = (7 - days.length % 7) % 7;
        for (let i = 1; i <= rem; i++) {
            const dt = new Date(this.vy, this.vm + 1, i);
            days.push({ d: dt.getDate(), curr: false, today: false, selected: false, disabled: this._dis(dt), ts: dt.getTime() });
        }
        return days;
    },

    get calendarMonths() {
        return this.MONTHS.map((name, i) => ({
            name, i, selected: this.sel && this.sel.getFullYear() === this.vy && this.sel.getMonth() === i,
        }));
    },

    get calendarYears() {
        const dec = Math.floor(this.vy / 10) * 10;
        const out = [];
        for (let y = dec - 1; y <= dec + 10; y++) {
            out.push({ year: y, curr: y >= dec && y < dec + 10, selected: this.sel && this.sel.getFullYear() === y });
        }
        return out;
    },

    _dis(dt) { return this.maxDate ? dt > this.maxDate : false; },

    openCalendar() {
        if (this.sel) { this.vy = this.sel.getFullYear(); this.vm = this.sel.getMonth(); }
        this.view = 'day'; this.open = true;
    },

    toggleView() {
        this.view = this.view === 'day' ? 'month' : this.view === 'month' ? 'year' : 'day';
    },

    prev() {
        if (this.view === 'day') { this.vm === 0 ? (this.vm=11, this.vy--) : this.vm--; }
        else if (this.view === 'month') this.vy--;
        else this.vy -= 10;
    },

    next() {
        if (this.view === 'day') { this.vm === 11 ? (this.vm=0, this.vy++) : this.vm++; }
        else if (this.view === 'month') this.vy++;
        else this.vy += 10;
    },

    pickDay(day) {
        if (day.disabled) return;
        this.sel = new Date(day.ts);
        if (!day.curr) { this.vy = this.sel.getFullYear(); this.vm = this.sel.getMonth(); }
        this.open = false;
    },

    pickMonth(m) { this.vm = m.i; this.view = 'day'; },
    pickYear(y)   { this.vy = y.year; this.view = 'month'; },

    today() {
        const t = new Date(); t.setHours(0,0,0,0);
        if (!this._dis(t)) { this.sel = t; this.vy = t.getFullYear(); this.vm = t.getMonth(); }
        this.open = false;
    },

    clear() { this.sel = null; this.open = false; },
}));

Alpine.start();

// ─── Global helpers ────────────────────────────────────────

// $notify(type, message [, title] [, duration])
// Examples:
//   $notify('success', 'Lưu thành công')
//   $notify('error', 'Có lỗi xảy ra', 'Lỗi hệ thống')
//   $notify('warning', 'Tồn kho thấp', null, 8000)
window.$notify = (type, message, title, duration) =>
    Alpine.store('toast').add(type, title ?? null, message, duration);

// $confirm(options) → Promise<boolean>
// Examples:
//   const ok = await $confirm({ variant: 'danger', title: 'Xóa?', message: '...' })
window.$confirm = (options) =>
    Alpine.store('dialog').open(options);

// $confirmDelete(formId | formElement, options?)
// Intercepts a form submission with a confirm dialog.
// Examples:
//   $confirmDelete('delete-form-42')
//   $confirmDelete($el.closest('form'), { title: 'Xóa danh mục?' })
window.$confirmDelete = async (formId, options = {}) => {
    const ok = await Alpine.store('dialog').open({
        variant:     'danger',
        title:       options.title       ?? 'Xác nhận xóa',
        message:     options.message     ?? 'Hành động này không thể hoàn tác.',
        confirmText: options.confirmText ?? 'Xóa',
        cancelText:  options.cancelText  ?? 'Hủy'
    });
    if (!ok) return;
    const form = typeof formId === 'string' ? document.getElementById(formId) : formId;
    if (form) form.submit();
};

// $confirmAction(options, callback)
// For non-form async actions.
// Example:
//   $confirmAction({ variant: 'warning', title: 'Duyệt phiếu?' }, () => approveForm.submit())
window.$confirmAction = async (options, callback) => {
    const ok = await Alpine.store('dialog').open(options);
    if (ok && typeof callback === 'function') callback();
};

// TomSelect / Chart
window.TomSelect = TomSelect;
window.Chart = Chart;

