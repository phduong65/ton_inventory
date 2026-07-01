@extends('layouts.admin')

@section('title', 'Lịch sử hoạt động')
@section('page-title', 'Lịch sử hoạt động')
@section('breadcrumb', 'Cài đặt / Lịch sử')

@section('content')

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
     style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

    {{-- Filter --}}
    <form method="GET" class="p-4 flex flex-wrap gap-3 items-end" style="border-bottom:1px solid var(--surface-border)">
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Từ ngày</label>
            <x-date-picker name="date_from" value="{{ request('date_from') }}" class="w-36" placeholder="Từ ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đến ngày</label>
            <x-date-picker name="date_to" value="{{ request('date_to') }}" class="w-36" placeholder="Đến ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Người dùng</label>
            <select name="causer_id" class="form-input h-9 text-sm w-44">
                <option value="">Tất cả</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('causer_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Hành động</label>
            <select name="description" class="form-input h-9 text-sm w-36">
                <option value="">Tất cả</option>
                @foreach($actions as $action)
                <option value="{{ $action }}" {{ request('description') === $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="h-9 px-4 text-sm font-medium rounded-xl self-end transition-colors"
                style="background:var(--surface-bg);border:1px solid var(--surface-border);color:var(--text-secondary)"
                onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
            <i class="ph ph-magnifying-glass mr-1"></i> Lọc
        </button>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide w-44" style="color:var(--text-muted)">Thời gian</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide w-36" style="color:var(--text-muted)">Người dùng</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide w-28" style="color:var(--text-muted)">Hành động</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Đối tượng</th>
                    <th class="px-5 py-3 w-12"></th>
                </tr>
            </thead>
            <tbody x-data>
                @forelse($logs as $log)
                <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors"
                    x-data="{ show: false }">
                    <td class="px-5 py-3.5 text-xs whitespace-nowrap" style="color:var(--text-muted)">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-5 py-3.5">
                        @if($log->causer)
                        <span class="text-xs font-medium" style="color:var(--text-primary)">{{ $log->causer->name }}</span>
                        @else
                        <span class="text-xs italic" style="color:var(--text-muted)">System</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @php
                        $badgeClass = match($log->description) {
                            'created'        => 'badge-green',
                            'updated'        => 'badge-blue',
                            'deleted'        => 'badge-red',
                            'approved'       => 'badge-green',
                            'rejected'       => 'badge-orange',
                            'submitted'      => 'badge-yellow',
                            'reset-password' => 'badge-purple',
                            default          => 'badge-gray',
                        };
                        @endphp
                        <span class="{{ $badgeClass }} inline-flex px-2 py-0.5 rounded-full text-xs font-medium">
                            {{ $log->description }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">
                        @if($log->subject_type && $log->subject_id)
                        <span class="font-mono">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                        @else
                        <span>—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        @if($log->properties && $log->properties->isNotEmpty())
                        <button @click="show = !show"
                                class="w-7 h-7 inline-flex items-center justify-center rounded-lg transition-colors"
                                style="color:var(--text-muted)"
                                onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                            <i class="bi text-[11px]" :class="show ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @if($log->properties && $log->properties->isNotEmpty())
                <tr x-show="show" x-data="{ show: false }" x-cloak style="background:var(--surface-bg)">
                    <td colspan="5" class="px-5 py-3">
                        <pre class="text-xs overflow-x-auto whitespace-pre-wrap font-mono" style="color:var(--text-secondary)">{{ json_encode($log->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center">
                        <i class="ph ph-clock-counter-clockwise text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                        <p class="text-sm" style="color:var(--text-muted)">Không có lịch sử hoạt động</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection
