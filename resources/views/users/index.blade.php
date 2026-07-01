@extends('layouts.admin')

@section('title', 'Người dùng')
@section('page-title', 'Người dùng')
@section('breadcrumb', 'Cài đặt / Người dùng')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editUser: null }">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5">
            <span class="text-sm font-medium" style="color:var(--text-secondary)">Tài khoản hệ thống</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                  style="background:rgba(99,102,241,0.10);color:#4f46e5">
                {{ $users->total() }}
            </span>
        </div>
        <button @click="openCreate = true"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors"
                style="background:#4f46e5"
                onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            <i class="ph ph-plus text-base"></i> Thêm người dùng
        </button>
    </div>

    {{-- ── Table ───────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden"
         style="box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--surface-bg);border-bottom:1px solid var(--surface-border)">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Người dùng</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Email</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Vai trò</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Ngày tạo</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="border-t border-gray-50 dark:border-gray-700/60 hover:bg-gray-50/70 dark:hover:bg-white/[0.025] transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 font-semibold text-xs text-white"
                                     style="background:#4f46e5">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-sm" style="color:var(--text-primary)">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $user->email }}</td>
                        <td class="px-5 py-3.5">
                            @foreach($user->roles as $role)
                            <span class="badge-indigo inline-flex text-xs font-medium px-2 py-0.5 rounded-full">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--text-muted)">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="editUser = {{ $user->toJson() }}; openEdit = true"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                        onmouseover="this.style.background='rgba(99,102,241,0.08)';this.style.color='#4f46e5'"
                                        onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                    <i class="ph ph-pencil-simple text-sm"></i>
                                </button>
                                <form action="{{ route('users.reset-password', $user) }}" method="POST"
                                      onsubmit="return confirm('Reset mật khẩu cho {{ $user->name }}?')">
                                    @csrf
                                    <button type="submit" title="Reset mật khẩu"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                            onmouseover="this.style.background='rgba(245,158,11,0.08)';this.style.color='#d97706'"
                                            onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                        <i class="ph ph-key text-sm"></i>
                                    </button>
                                </form>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Xóa người dùng «{{ $user->name }}»?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors" style="color:var(--text-muted)"
                                            onmouseover="this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'"
                                            onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                                        <i class="ph ph-trash text-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <i class="ph ph-users text-3xl block mb-2" style="color:var(--text-muted);opacity:.35"></i>
                            <p class="text-sm" style="color:var(--text-muted)">Chưa có người dùng</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-5 py-3" style="border-top:1px solid var(--surface-border)">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- ── Edit Modal ───────────────────────────────────────── --}}
    <div x-show="openEdit"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openEdit = false"></div>
        <div class="modal-panel relative w-full max-w-md p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                        <i class="ph ph-pencil-simple text-sm" style="color:#4f46e5"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Sửa người dùng</h3>
                </div>
                <button @click="openEdit = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <template x-if="editUser">
                <form :action="`/users/${editUser.id}`" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên</label>
                        <input type="text" name="name" :value="editUser.name" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Email</label>
                        <input type="email" name="email" :value="editUser.email" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Vai trò</label>
                        <select name="role" required class="form-input">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                    :selected="editUser.roles && editUser.roles.length && editUser.roles[0].name === '{{ $role->name }}'">
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="openEdit = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Lưu</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ── Create Modal ─────────────────────────────────────── --}}
    <div x-show="openCreate"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(3px)" @click="openCreate = false"></div>
        <div class="modal-panel relative w-full max-w-md p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.10)">
                        <i class="ph ph-user-plus text-sm" style="color:#4f46e5"></i>
                    </div>
                    <h3 class="font-semibold text-base" style="color:var(--text-primary)">Thêm người dùng</h3>
                </div>
                <button @click="openCreate = false" class="w-8 h-8 inline-flex items-center justify-center rounded-lg" style="color:var(--text-muted)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Tên</label>
                    <input type="text" name="name" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Email</label>
                    <input type="email" name="email" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Mật khẩu</label>
                    <input type="password" name="password" required minlength="8" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Xác nhận mật khẩu</label>
                    <input type="password" name="password_confirmation" required class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-muted)">Vai trò</label>
                    <select name="role" required class="form-input">
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="openCreate = false" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" style="border-color:var(--surface-border);color:var(--text-secondary)" onmouseover="this.style.background='var(--surface-bg)'" onmouseout="this.style.background='transparent'">Hủy</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl" style="background:#4f46e5" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Tạo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
