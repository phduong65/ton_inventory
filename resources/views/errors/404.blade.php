@extends('layouts.admin')

@section('title', '404 - Không tìm thấy trang')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[50vh] text-center">
    <p class="text-8xl font-bold text-gray-200 dark:text-gray-700">404</p>
    <h1 class="mt-4 text-xl font-semibold text-gray-700 dark:text-gray-300">Không tìm thấy trang</h1>
    <p class="mt-2 text-gray-500 text-sm">Trang bạn đang tìm không tồn tại hoặc đã bị xóa.</p>
    <a href="{{ url('/') }}" class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
        <i class="bi bi-house"></i> Về trang chủ
    </a>
</div>
@endsection
