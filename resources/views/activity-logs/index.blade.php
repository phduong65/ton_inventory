@extends('layouts.admin')

@section('title', 'Lịch sử hoạt động')
@section('page-title', 'Lịch sử hoạt động')
@section('breadcrumb', 'Cài đặt / Lịch sử')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- Filter --}}
    <form method="GET" class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Từ ngày</label>
            <x-date-picker name="date_from" value="{{ request('date_from') }}" class="w-36" placeholder="Từ ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Đến ngày</label>
            <x-date-picker name="date_to" value="{{ request('date_to') }}" class="w-36" placeholder="Đến ngày" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Người dùng</label>
            <select name="causer_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-44">
                <option value="">Tất cả</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('causer_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-500 dark:text-gray-400">Hành động</label>
            <select name="description" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-36">
                <option value="">Tất cả</option>
                @foreach($actions as $action)
                <option value="{{ $action }}" {{ request('description') === $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-lg self-end">
            <i class="bi bi-search mr-1"></i> Lọc
        </button>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3 w-40">Thời gian</th>
                    <th class="px-4 py-3 w-36">Người dùng</th>
                    <th class="px-4 py-3 w-28">Hành động</th>
                    <th class="px-4 py-3">Đối tượng</th>
                    <th class="px-4 py-3 w-12"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-data>
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50" x-data="{ show: false }">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-4 py-3">
                        @if($log->causer)
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-xs">{{ $log->causer->name }}</span>
                        @else
                        <span class="text-gray-400 text-xs italic">System</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $badgeClass = match($log->description) {
                                'created'        => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'updated'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'deleted'        => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                'approved'       => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'rejected'       => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                'submitted'      => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'reset-password' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                default          => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
                            {{ $log->description }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                        @if($log->subject_type && $log->subject_id)
                        <span class="font-mono">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($log->properties && $log->properties->isNotEmpty())
                        <button @click="show = !show" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i class="bi" :class="show ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size:11px"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @if($log->properties && $log->properties->isNotEmpty())
                <tr x-show="show" x-data="{ show: false }" x-cloak class="bg-gray-50 dark:bg-gray-900/30">
                    <td colspan="5" class="px-4 py-3">
                        <pre class="text-xs text-gray-600 dark:text-gray-400 overflow-x-auto whitespace-pre-wrap font-mono">{{ json_encode($log->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                        <i class="ph-clock-counter-clockwise text-4xl block mb-2"></i>
                        Không có lịch sử hoạt động
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection
