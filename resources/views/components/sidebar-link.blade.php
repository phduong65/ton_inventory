@props(['route', 'icon', 'label', 'params' => []])

@php
$url     = route($route, $params);
$current = request()->fullUrlIs($url) || request()->routeIs(explode('.', $route)[0] . '.*');
@endphp

<a href="{{ $url }}"
   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
          {{ $current
             ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 font-medium'
             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
    <i class="{{ $icon }} text-base"></i>
    {{ $label }}
</a>
