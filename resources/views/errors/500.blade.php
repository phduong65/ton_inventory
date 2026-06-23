@extends('layouts.admin')

@section('title', '500 - Lỗi máy chủ')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[50vh] text-center">
    <p class="text-8xl font-bold text-gray-200 dark:text-gray-700">500</p>
    <h1 class="mt-4 text-xl font-semibold text-gray-700 dark:text-gray-300">Lỗi máy chủ</h1>
    <p class="mt-2 text-gray-500 text-sm">Đã xảy ra lỗi phía máy chủ. Vui lòng thử lại sau.</p>
    <a href="{{ url('/') }}" class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
        <i class="bi bi-house"></i> Về trang chủ
    </a>
</div>
@endsection
