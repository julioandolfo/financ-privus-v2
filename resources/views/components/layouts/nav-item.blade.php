@props(['href' => '#', 'active' => false])

<a href="{{ $href }}"
    @class([
        'flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-colors',
        'bg-primary-600 text-white' => $active,
        'text-surface-400 hover:bg-surface-800 hover:text-white' => !$active,
    ])
>
    <span class="flex-shrink-0">{{ $icon }}</span>
    <span class="truncate">{{ $slot }}</span>
</a>
