<!DOCTYPE html>
<html lang="vi" class="{{ auth()->user()?->theme === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trang chủ') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- iOS Safari zooms on inputs with font-size < 16px; force 16px on iOS only --}}
    <script>if(/iPad|iPhone|iPod/.test(navigator.userAgent)){document.documentElement.classList.add('ios')}</script>
    <style>.ios input,.ios textarea,.ios select{font-size:16px!important}.ios .ts-wrapper .ts-control input{font-size:16px!important}</style>
</head>
<body class="font-sans" x-data="{ mobileMenuOpen: false, sidebarCollapsed: false }">

@php
$routeName = request()->route()?->getName() ?? '';
$activeModule = match(true) {
    str_starts_with($routeName, 'categories')
    || str_starts_with($routeName, 'products')
    || str_starts_with($routeName, 'units')
    || str_starts_with($routeName, 'suppliers')
    || str_starts_with($routeName, 'destinations') => 'danhmuc',
    str_starts_with($routeName, 'transactions')
    || str_starts_with($routeName, 'stocktakes')   => 'phieu',
    str_starts_with($routeName, 'inventory')
    || str_starts_with($routeName, 'stock-ledger') => 'kho',
    str_starts_with($routeName, 'reports')         => 'baocao',
    str_starts_with($routeName, 'users')
    || str_starts_with($routeName, 'activity-logs')
    || str_starts_with($routeName, 'settings')
    || str_starts_with($routeName, 'profile')      => 'caidat',
    default => 'tongquan',
};
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    @include('components.sidebar', ['activeModule' => $activeModule, 'routeName' => $routeName])

    {{-- Right column --}}
    <div class="flex flex-col flex-1 min-w-0">

        {{-- Topbar --}}
        @include('components.topbar', ['activeModule' => $activeModule])

        {{-- Quick Actions (all pages) --}}
        @include('components.quick-actions')

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto" style="background:#f0f4f9">
            <div class="p-4 md:p-6">
                @include('components.flash-messages')
                @yield('content')
            </div>
        </main>

    </div>
</div>

@stack('scripts')

@include('components.toast')
@include('components.dialog')
</body>
</html>
