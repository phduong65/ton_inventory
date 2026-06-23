@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
<div class="w-full max-w-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-primary-100 dark:bg-primary-900/30 rounded-2xl mb-4">
                <i class="bi bi-boxes text-primary-600 dark:text-primary-400 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Đăng nhập vào hệ thống</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-3 py-2.5 text-sm border rounded-lg bg-white dark:bg-gray-700
                              text-gray-900 dark:text-white border-gray-300 dark:border-gray-600
                              focus:ring-2 focus:ring-primary-500 focus:border-transparent
                              @error('email') border-red-500 @enderror">
                @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mật khẩu</label>
                <input type="password" name="password" required
                       class="w-full px-3 py-2.5 text-sm border rounded-lg bg-white dark:bg-gray-700
                              text-gray-900 dark:text-white border-gray-300 dark:border-gray-600
                              focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" name="remember" class="rounded">
                    Ghi nhớ đăng nhập
                </label>
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                Đăng nhập
            </button>
        </form>
    </div>
</div>
@endsection
