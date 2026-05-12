@props(['label', 'value', 'trend' => null, 'trendLabel' => null, 'icon' => null, 'color' => 'primary'])

@php
$colors = [
    'primary' => 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400',
    'green'   => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
    'red'     => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
    'yellow'  => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
];
@endphp

<x-ui.card>
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400 truncate">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-surface-900 dark:text-white tracking-tight">{{ $value }}</p>

            @if($trend !== null)
            <div class="mt-1 flex items-center gap-1">
                @if($trend > 0)
                <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                </svg>
                <span class="text-xs text-green-600 dark:text-green-400 font-medium">+{{ $trend }}%</span>
                @else
                <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" />
                </svg>
                <span class="text-xs text-red-600 dark:text-red-400 font-medium">{{ $trend }}%</span>
                @endif
                @if($trendLabel)
                <span class="text-xs text-surface-400">{{ $trendLabel }}</span>
                @endif
            </div>
            @endif
        </div>

        @if($icon)
        <div class="flex-shrink-0 p-3 rounded-xl {{ $colors[$color] }}">
            {!! $icon !!}
        </div>
        @endif
    </div>
</x-ui.card>
