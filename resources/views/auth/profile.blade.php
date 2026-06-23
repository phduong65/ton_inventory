@extends('layouts.admin')

@section('title', 'Hồ sơ cá nhân')
@section('page-title', 'Hồ sơ cá nhân')
@section('breadcrumb', 'Hồ sơ')

@section('content')
<div class="max-w-xl space-y-5">
    {{-- Change Password --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Đổi mật khẩu</h2>
        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mật khẩu mới</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Xác nhận mật khẩu mới</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div class="pt-2">
                <button type="submit" class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Đổi mật khẩu
                </button>
            </div>
        </form>
    </div>

    {{-- Theme --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Giao diện</h2>
        <div class="flex gap-3">
            <form action="{{ route('profile.theme') }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="theme" value="light">
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg border {{ auth()->user()->theme === 'light' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-300 text-gray-700' }}">
                    <i class="bi bi-sun mr-1"></i> Sáng
                </button>
            </form>
            <form action="{{ route('profile.theme') }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="theme" value="dark">
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg border {{ auth()->user()->theme === 'dark' ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'border-gray-300 text-gray-700 dark:text-gray-300 dark:border-gray-600' }}">
                    <i class="bi bi-moon mr-1"></i> Tối
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
