@extends('layouts.admin')

@section('title', 'Cài đặt hệ thống')
@section('page-title', 'Cài đặt hệ thống')
@section('breadcrumb', 'Hệ thống / Cài đặt')

@section('content')
<div class="max-w-2xl space-y-5">

    {{-- Thông tin công ty --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="px-6 py-4" style="border-bottom:1px solid var(--surface-border)">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                    <i class="ph ph-buildings text-sm" style="color:#4f46e5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Thông tin công ty</h3>
                    <p class="text-xs" style="color:var(--text-muted)">Hiển thị trên phiếu in nhập/xuất kho và kiểm kê</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-5">
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="require_approval" value="{{ $requireApproval ? '1' : '0' }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên công ty</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}"
                               class="form-input" placeholder="VD: Công ty TNHH F&B Việt Nam">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Mã số thuế</label>
                        <input type="text" name="company_tax_code" value="{{ old('company_tax_code', $companyTaxCode) }}"
                               class="form-input" placeholder="0123456789">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Số điện thoại</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $companyPhone) }}"
                               class="form-input" placeholder="028 1234 5678">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Địa chỉ</label>
                        <input type="text" name="company_address" value="{{ old('company_address', $companyAddress) }}"
                               class="form-input" placeholder="123 Đường ABC, Quận 1, TP.HCM">
                    </div>
                </div>
                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                            style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                        Lưu thông tin
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quy trình phiếu --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">

        <div class="px-6 py-4" style="border-bottom:1px solid var(--surface-border)">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(245,158,11,0.10)">
                    <i class="ph ph-flow-arrow text-sm" style="color:#d97706"></i>
                </div>
                <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Quy trình phiếu nhập / xuất kho</h3>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <p class="text-sm font-medium mb-2" style="color:var(--text-primary)">Yêu cầu duyệt phiếu</p>
                    <p class="text-xs leading-relaxed" style="color:var(--text-secondary)">
                        <strong>Bật:</strong>
                        Phiếu phải qua trạng thái
                        <span class="font-mono px-1.5 py-0.5 rounded" style="background:rgba(217,119,6,0.10);color:#d97706">Chờ duyệt</span>
                        trước khi tác động vào kho.<br>
                        <strong>Tắt:</strong>
                        Phiếu được xác nhận ngay khi lưu, không cần người duyệt.
                    </p>
                </div>

                <form action="{{ route('settings.update') }}" method="POST" id="setting-form" class="flex-shrink-0 mt-1">
                    @csrf @method('PUT')
                    <input type="hidden" name="require_approval" id="require-approval-value"
                           value="{{ $requireApproval ? '1' : '0' }}">
                    <button type="button"
                            onclick="
                                var inp = document.getElementById('require-approval-value');
                                inp.value = inp.value === '1' ? '0' : '1';
                                document.getElementById('setting-form').submit();
                            "
                            title="{{ $requireApproval ? 'Nhấn để TẮT duyệt' : 'Nhấn để BẬT duyệt' }}"
                            style="position:relative;display:inline-flex;align-items:center;width:44px;height:24px;border-radius:9999px;border:none;cursor:pointer;background:{{ $requireApproval ? '#16a34a' : '#9ca3af' }};transition:background .2s;padding:0">
                        <span style="display:block;width:18px;height:18px;border-radius:9999px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.3);transition:transform .2s;transform:{{ $requireApproval ? 'translateX(23px)' : 'translateX(3px)' }}"></span>
                    </button>
                </form>
            </div>

            <div class="mt-4">
                @if($requireApproval)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium" style="background:rgba(251,191,36,0.10);color:#a16207;border:1px solid rgba(251,191,36,0.25)">
                    <i class="ph ph-shield-check text-sm"></i>
                    Quy trình: NHÁP → CHỜ DUYỆT → ĐÃ DUYỆT
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium" style="background:rgba(16,185,129,0.10);color:#059669;border:1px solid rgba(16,185,129,0.25)">
                    <i class="ph ph-lightning text-sm"></i>
                    Quy trình: NHÁP → ĐÃ DUYỆT (xác nhận ngay)
                </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
