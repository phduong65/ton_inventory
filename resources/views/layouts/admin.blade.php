<!DOCTYPE html>
<html lang="vi" class="{{ auth()->user()?->theme === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trang chủ') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans" x-data>

@php
$routeName = request()->route()?->getName() ?? '';
$activeModule = match(true) {
    str_starts_with($routeName, 'categories')
    || str_starts_with($routeName, 'products')
    || str_starts_with($routeName, 'suppliers')    => 'danhmuc',
    str_starts_with($routeName, 'transactions')
    || str_starts_with($routeName, 'stocktakes')   => 'phieu',
    str_starts_with($routeName, 'inventory')
    || str_starts_with($routeName, 'stock-ledger') => 'kho',
    str_starts_with($routeName, 'reports')         => 'baocao',
    str_starts_with($routeName, 'users')
    || str_starts_with($routeName, 'activity-logs')
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

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto" style="background:var(--surface-bg)">
            <div class="p-6">
                @include('components.flash-messages')
                @yield('content')
            </div>
        </main>

    </div>
</div>

@stack('scripts')
</body>
</html>
