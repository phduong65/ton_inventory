@extends('layouts.admin')

@section('title', 'Cài đặt hệ thống')
@section('page-title', 'Cài đặt hệ thống')
@section('breadcrumb', 'Hệ thống / Cài đặt')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">

        <div class="px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Quy trình phiếu nhập / xuất kho</h3>
        </div>

        <div class="px-6 py-4 space-y-4">

            {{-- Toggle button --}}
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:24px">

                <div>
                    <p style="font-size:14px; font-weight:500; color:var(--text-primary); margin-bottom:6px">
                        Yêu cầu duyệt phiếu
                    </p>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6">
                        <strong>Bật:</strong>
                        Phiếu phải qua trạng thái <span style="font-family:monospace; color:#d97706">Chờ duyệt</span>
                        trước khi tác động vào kho.<br>
                        <strong>Tắt:</strong>
                        Phiếu được xác nhận ngay khi lưu, không cần người duyệt.
                    </p>
                </div>

                {{-- Toggle button (submit form ngay khi click) --}}
                <form action="{{ route('settings.update') }}" method="POST" id="setting-form" style="flex-shrink:0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="require_approval" id="require-approval-value"
                           value="{{ $requireApproval ? '1' : '0' }}">

                    <button type="button"
                            onclick="
                                var inp = document.getElementById('require-approval-value');
                                inp.value = inp.value === '1' ? '0' : '1';
                                document.getElementById('setting-form').submit();
                            "
                            title="{{ $requireApproval ? 'Nhấn để TẮT duyệt' : 'Nhấn để BẬT duyệt' }}"
                            style="
                                position: relative;
                                display: inline-flex;
                                align-items: center;
                                width: 44px;
                                height: 24px;
                                border-radius: 9999px;
                                border: none;
                                cursor: pointer;
                                background: {{ $requireApproval ? '#16a34a' : '#9ca3af' }};
                                transition: background .2s;
                                margin-top: 2px;
                                padding: 0;
                            ">
                        <span style="
                            display: block;
                            width: 18px;
                            height: 18px;
                            border-radius: 9999px;
                            background: #fff;
                            box-shadow: 0 1px 3px rgba(0,0,0,.3);
                            transition: transform .2s;
                            transform: {{ $requireApproval ? 'translateX(23px)' : 'translateX(3px)' }};
                        "></span>
                    </button>
                </form>

            </div>

            {{-- Trạng thái hiện tại --}}
            <div style="margin-top:16px">
                @if($requireApproval)
                    <div style="
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 6px 12px;
                        border-radius: 8px;
                        font-size: 12px;
                        font-weight: 500;
                        background: #fefce8;
                        color: #a16207;
                        border: 1px solid #fde68a;
                    ">
                        <i class="bi bi-shield-check"></i>
                        Quy trình: NHÁP → CHỜ DUYỆT → ĐÃ DUYỆT
                    </div>
                @else
                    <div style="
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 6px 12px;
                        border-radius: 8px;
                        font-size: 12px;
                        font-weight: 500;
                        background: #f0fdf4;
                        color: #15803d;
                        border: 1px solid #bbf7d0;
                    ">
                        <i class="bi bi-lightning-charge"></i>
                        Quy trình: NHÁP → ĐÃ DUYỆT (xác nhận ngay)
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
