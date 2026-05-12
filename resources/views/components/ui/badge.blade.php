@props(['variant' => 'default'])

@php
$variants = [
    'default' => 'bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300',
    'primary' => 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300',
    'success' => 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300',
    'warning' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
    'danger'  => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300',
    'info'    => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $variants[$variant]]) }}>
    {{ $slot }}
</span>
