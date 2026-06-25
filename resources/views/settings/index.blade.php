@extends('layouts.admin')

@section('title', 'Cài đặt hệ thống')
@section('page-title', 'Cài đặt hệ thống')
@section('breadcrumb', 'Hệ thống / Cài đặt')

@section('content')
<div class="max-w-2xl space-y-5">

    {{-- Thông tin công ty --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Thông tin công ty</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Hiển thị trên phiếu in nhập/xuất kho và kiểm kê</p>
        </div>
        <div class="px-6 py-4">
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="require_approval" value="{{ $requireApproval ? '1' : '0' }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tên công ty</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="VD: Công ty TNHH F&B Việt Nam">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mã số thuế</label>
                        <input type="text" name="company_tax_code" value="{{ old('company_tax_code', $companyTaxCode) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="0123456789">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Số điện thoại</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $companyPhone) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="028 1234 5678">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa chỉ</label>
                        <input type="text" name="company_address" value="{{ old('company_address', $companyAddress) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="123 Đường ABC, Quận 1, TP.HCM">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        Lưu thông tin
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quy trình phiếu --}}
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
