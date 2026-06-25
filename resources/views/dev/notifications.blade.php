@extends('layouts.admin')

@section('title', 'Demo Thông Báo')
@section('page-title', 'Demo Toast & Dialog')
@section('breadcrumb', 'Dev / Thông Báo')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data>

    {{-- ── Toast Section ─────────────────────────────────── --}}
    <div class="demo-card">
        <div class="demo-card-header">
            <div class="demo-card-icon" style="background:#eef2ff; color:#6366f1">
                <i class="bi bi-bell-fill" style="font-size:18px"></i>
            </div>
            <div>
                <h2 class="demo-card-title">Toast Notifications</h2>
                <p class="demo-card-desc">Hiển thị góc dưới phải, tự động biến mất sau 4 giây</p>
            </div>
        </div>

        {{-- Basic variants --}}
        <p class="demo-group-label">Variants cơ bản</p>
        <div class="grid grid-cols-2 gap-3">
            <button class="demo-btn demo-btn-success"
                    @click="$notify('success', 'Lưu dữ liệu thành công')">
                <i class="bi bi-check-circle-fill"></i> Success
            </button>
            <button class="demo-btn demo-btn-error"
                    @click="$notify('error', 'Không thể kết nối máy chủ')">
                <i class="bi bi-x-circle-fill"></i> Error
            </button>
            <button class="demo-btn demo-btn-warning"
                    @click="$notify('warning', 'Tồn kho dưới mức tối thiểu')">
                <i class="bi bi-exclamation-triangle-fill"></i> Warning
            </button>
            <button class="demo-btn demo-btn-info"
                    @click="$notify('info', 'Phiên của bạn sẽ hết hạn sau 5 phút')">
                <i class="bi bi-info-circle-fill"></i> Info
            </button>
        </div>

        {{-- With title --}}
        <p class="demo-group-label mt-5">Có tiêu đề (title + message)</p>
        <div class="grid grid-cols-2 gap-3">
            <button class="demo-btn demo-btn-success"
                    @click="$notify('success', 'Phiếu nhập kho #PN-2025-001 đã được duyệt và tồn kho đã cập nhật.', 'Duyệt thành công')">
                <i class="bi bi-check2-all"></i> Success + title
            </button>
            <button class="demo-btn demo-btn-error"
                    @click="$notify('error', 'Số lượng xuất vượt quá tồn kho hiện tại. Vui lòng kiểm tra lại.', 'Lỗi duyệt phiếu')">
                <i class="bi bi-shield-x"></i> Error + title
            </button>
            <button class="demo-btn demo-btn-warning"
                    @click="$notify('warning', 'Còn 3 sản phẩm sắp hết hàng. Cần đặt hàng bổ sung.', 'Cảnh báo tồn kho')">
                <i class="bi bi-box-seam"></i> Warning + title
            </button>
            <button class="demo-btn demo-btn-info"
                    @click="$notify('info', 'Báo cáo tháng 5/2025 đã được xuất thành công.', 'Export hoàn tất')">
                <i class="bi bi-file-earmark-check"></i> Info + title
            </button>
        </div>

        {{-- Special --}}
        <p class="demo-group-label mt-5">Trường hợp đặc biệt</p>
        <div class="grid grid-cols-2 gap-3">
            <button class="demo-btn demo-btn-neutral"
                    @click="$notify('info', 'Toast này không tự đóng. Nhấn X để đóng thủ công.', 'Không tự đóng', 0)">
                <i class="bi bi-pin-angle"></i> Persistent (duration = 0)
            </button>
            <button class="demo-btn demo-btn-neutral"
                    @click="$notify('success', 'Toast sẽ tắt sau 8 giây', null, 8000)">
                <i class="bi bi-clock-history"></i> 8 giây (custom duration)
            </button>
            <button class="demo-btn demo-btn-neutral"
                    @click="['success','error','warning','info'].forEach((t,i) => setTimeout(() => $notify(t, t.charAt(0).toUpperCase()+t.slice(1)+' — thông báo số '+(i+1)), i * 350))">
                <i class="bi bi-stack"></i> 4 toast cùng lúc
            </button>
            <button class="demo-btn demo-btn-neutral"
                    @click="for(let i=1;i<=5;i++) setTimeout(()=>$notify('info','Thông báo thứ '+i+'/5 — kiểm tra max 5 items',null), i*200)">
                <i class="bi bi-layers"></i> 5 toast (test max)
            </button>
        </div>
    </div>

    {{-- ── Dialog Section ───────────────────────────────── --}}
    <div class="demo-card">
        <div class="demo-card-header">
            <div class="demo-card-icon" style="background:#fff1f2; color:#ef4444">
                <i class="bi bi-shield-exclamation" style="font-size:18px"></i>
            </div>
            <div>
                <h2 class="demo-card-title">Confirm Dialogs</h2>
                <p class="demo-card-desc">Modal xác nhận, trả về Promise — dùng với await</p>
            </div>
        </div>

        {{-- Variants --}}
        <p class="demo-group-label">Variants cơ bản</p>
        <div class="grid grid-cols-2 gap-3">
            <button class="demo-btn demo-btn-info"
                    @click="
                        const ok = await $confirm({
                            variant: 'info',
                            title: 'Thông tin',
                            message: 'Đây là dialog dạng info, dùng cho thông báo trung tính.',
                            confirmText: 'Đã hiểu'
                        });
                        $notify(ok ? 'info' : 'warning', ok ? 'Bạn đã nhấn Đã hiểu' : 'Bạn đã hủy')
                    ">
                <i class="bi bi-info-circle-fill"></i> Info dialog
            </button>
            <button class="demo-btn demo-btn-success"
                    @click="
                        const ok = await $confirm({
                            variant: 'success',
                            title: 'Xác nhận duyệt phiếu',
                            message: 'Phiếu nhập kho sẽ được duyệt và tồn kho sẽ được cập nhật ngay lập tức.',
                            confirmText: 'Duyệt ngay'
                        });
                        $notify(ok ? 'success' : 'warning', ok ? 'Phiếu đã được duyệt' : 'Đã hủy duyệt')
                    ">
                <i class="bi bi-check-circle-fill"></i> Success dialog
            </button>
            <button class="demo-btn demo-btn-warning"
                    @click="
                        const ok = await $confirm({
                            variant: 'warning',
                            title: 'Cảnh báo tồn kho',
                            message: 'Số lượng xuất gần đạt giới hạn tồn kho. Bạn có chắc muốn tiếp tục?',
                            confirmText: 'Vẫn tiếp tục',
                            cancelText: 'Kiểm tra lại'
                        });
                        $notify(ok ? 'warning' : 'info', ok ? 'Tiếp tục xử lý' : 'Đã quay lại kiểm tra')
                    ">
                <i class="bi bi-exclamation-triangle-fill"></i> Warning dialog
            </button>
            <button class="demo-btn demo-btn-error"
                    @click="
                        const ok = await $confirm({
                            variant: 'danger',
                            title: 'Xác nhận xóa',
                            message: 'Sản phẩm này sẽ bị xóa vĩnh viễn và không thể khôi phục.',
                            confirmText: 'Xóa',
                            cancelText: 'Hủy'
                        });
                        $notify(ok ? 'error' : 'info', ok ? 'Đã xóa sản phẩm' : 'Đã hủy xóa')
                    ">
                <i class="bi bi-trash3-fill"></i> Danger dialog
            </button>
        </div>

        {{-- Real-world patterns --}}
        <p class="demo-group-label mt-5">Pattern thực tế</p>
        <div class="grid grid-cols-2 gap-3">
            <button class="demo-btn demo-btn-error"
                    @click="$confirmDelete('demo-delete-form', { title: 'Xóa nhà cung cấp?', message: 'Nhà cung cấp Vinamilk và toàn bộ lịch sử giao dịch sẽ bị xóa.' })">
                <i class="bi bi-person-x-fill"></i> $confirmDelete (form)
            </button>
            <button class="demo-btn demo-btn-warning"
                    @click="$confirmAction({ variant: 'warning', title: 'Hủy phiếu xuất?', message: 'Phiếu #PX-2025-088 đang ở trạng thái PENDING sẽ bị hủy.', confirmText: 'Hủy phiếu' }, () => $notify('warning', 'Phiếu #PX-2025-088 đã bị hủy', 'Đã hủy phiếu'))">
                <i class="bi bi-file-x-fill"></i> $confirmAction (callback)
            </button>
            <button class="demo-btn demo-btn-info"
                    @click="
                        const ok = await $confirm({ variant: 'info', title: 'Không có message', confirmText: 'OK' });
                        $notify(ok ? 'success' : 'info', ok ? 'Confirmed' : 'Cancelled')
                    ">
                <i class="bi bi-chat-square"></i> Không có message
            </button>
            <button class="demo-btn demo-btn-neutral"
                    @click="
                        const ok = await $confirm({ variant: 'danger', title: 'Nhấn backdrop để đóng', message: 'Click vào vùng mờ bên ngoài để hủy.' });
                        $notify(ok ? 'success' : 'info', ok ? 'Xác nhận' : 'Đóng bằng backdrop/ESC')
                    ">
                <i class="bi bi-box-arrow-in-left"></i> Backdrop / ESC close
            </button>
        </div>
    </div>

    {{-- Hidden demo form --}}
    <form id="demo-delete-form" action="#" method="POST" style="display:none">
        @csrf @method('DELETE')
    </form>

</div>
@endsection
