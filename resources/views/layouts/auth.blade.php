<!DOCTYPE html>
<html lang="vi" class="{{ auth()->check() && auth()->user()->theme === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Đăng nhập') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>if(/iPad|iPhone|iPod/.test(navigator.userAgent)){document.documentElement.classList.add('ios')}</script>
    <style>.ios input,.ios textarea,.ios select{font-size:16px!important}</style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
    @yield('content')
</body>
</html>
