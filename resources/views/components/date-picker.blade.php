@props(['name', 'value' => '', 'placeholder' => 'Chọn ngày', 'maxDate' => null])

<div x-data="datePicker({ value: '{{ $value }}', maxDate: '{{ $maxDate ?? '' }}' })"
     class="relative"
     @click.outside="open = false"
     @keydown.escape.window="if(open) open = false">

    <input type="hidden" name="{{ $name }}" :value="hiddenValue">

    <button type="button" {{ $attributes->merge(['class' => 'dp-trigger']) }}
            @click="openCalendar()">
        <i class="bi bi-calendar3 dp-trigger-icon"></i>
        <span x-text="displayValue || '{{ $placeholder }}'"
              :class="sel ? '' : 'dp-trigger-ph'"></span>
    </button>

    {{-- Calendar popup --}}
    <div x-show="open" x-cloak style="display:none" class="dp-calendar">

        {{-- Header --}}
        <div class="dp-header">
            <button type="button" class="dp-nav-btn" @click.stop="prev()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>
            <button type="button" class="dp-title-btn" @click.stop="toggleView()" x-text="headerLabel"></button>
            <button type="button" class="dp-nav-btn" @click.stop="next()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
        </div>

        {{-- Day view --}}
        <div x-show="view === 'day'">
            <div class="dp-weekdays">
                <template x-for="d in DAYS" :key="d">
                    <span x-text="d"></span>
                </template>
            </div>
            <div class="dp-days">
                <template x-for="(day, idx) in calendarDays" :key="idx">
                    <button type="button" class="dp-day"
                            :class="{
                                'dp-day-other':    !day.curr,
                                'dp-day-today':    day.today && !day.selected,
                                'dp-day-selected': day.selected,
                                'dp-day-disabled': day.disabled,
                            }"
                            :disabled="day.disabled"
                            @click.stop="pickDay(day)"
                            x-text="day.d">
                    </button>
                </template>
            </div>
        </div>

        {{-- Month view --}}
        <div x-show="view === 'month'" class="dp-months">
            <template x-for="m in calendarMonths" :key="m.i">
                <button type="button" class="dp-month"
                        :class="{ 'dp-selected': m.selected }"
                        @click.stop="pickMonth(m)"
                        x-text="m.name">
                </button>
            </template>
        </div>

        {{-- Year view --}}
        <div x-show="view === 'year'" class="dp-years">
            <template x-for="y in calendarYears" :key="y.year">
                <button type="button" class="dp-year"
                        :class="{ 'dp-selected': y.selected, 'dp-other': !y.curr }"
                        @click.stop="pickYear(y)"
                        x-text="y.year">
                </button>
            </template>
        </div>

        {{-- Footer --}}
        <div class="dp-footer">
            <button type="button" class="dp-today-btn" @click.stop="today()">Hôm nay</button>
            <button type="button" class="dp-clear-btn" @click.stop="clear()">Xóa</button>
        </div>
    </div>
</div>
