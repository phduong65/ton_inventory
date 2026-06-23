@foreach (['success' => 'green', 'error' => 'red', 'warning' => 'yellow', 'info' => 'blue'] as $type => $color)
    @if(session($type))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-4 flex items-start gap-3 px-4 py-3 rounded-lg border
                bg-{{ $color }}-50 border-{{ $color }}-200 text-{{ $color }}-800
                dark:bg-{{ $color }}-900/20 dark:border-{{ $color }}-800 dark:text-{{ $color }}-400">
        <i class="bi {{ $color === 'green' ? 'bi-check-circle' : ($color === 'red' ? 'bi-x-circle' : 'bi-info-circle') }} mt-0.5 shrink-0"></i>
        <span class="text-sm">{{ session($type) }}</span>
        <button @click="show = false" class="ml-auto text-{{ $color }}-600 hover:text-{{ $color }}-800">
            <i class="bi bi-x"></i>
        </button>
    </div>
    @endif
@endforeach
