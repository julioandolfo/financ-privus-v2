@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-surface-800 rounded-2xl shadow-sm border border-surface-200 dark:border-surface-700 ' . ($padding ? 'p-5 lg:p-6' : '')]) }}>
    {{ $slot }}
</div>
