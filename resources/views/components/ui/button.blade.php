@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
    'href' => null,
    'loading' => false,
])

@php
$tag = $href ? 'a' : $as;
$variants = [
    'primary'   => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-sm',
    'secondary' => 'bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-200 hover:bg-surface-200 dark:hover:bg-surface-600 focus:ring-surface-500',
    'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
    'ghost'     => 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700 focus:ring-surface-500',
    'outline'   => 'border border-surface-200 dark:border-surface-700 text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-800 focus:ring-surface-500',
];
$sizes = [
    'sm' => 'px-3 py-1.5 text-xs gap-1.5',
    'md' => 'px-4 py-2.5 text-sm gap-2',
    'lg' => 'px-5 py-3 text-base gap-2.5',
];
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center rounded-xl font-medium transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed ' . $variants[$variant] . ' ' . $sizes[$size]
    ]) }}
>
    @if($loading)
    <svg class="animate-spin -ml-0.5 w-4 h-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    @endif
    {{ $slot }}
</{{ $tag }}>
