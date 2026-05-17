@props(['label' => null, 'error' => null, 'hint' => null])

<div class="w-full">
    @if($label)
    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
        {{ $label }}
        @if($attributes->get('required'))
        <span class="text-red-500 ml-0.5">*</span>
        @endif
    </label>
    @endif

    <textarea
        {{ $attributes->merge(['class' => '
            block w-full rounded-xl border-0 py-2.5 px-3.5 text-sm
            bg-white dark:bg-surface-800
            text-surface-900 dark:text-white
            ring-1 ring-inset ring-surface-200 dark:ring-surface-700
            placeholder:text-surface-400
            focus:ring-2 focus:ring-primary-500 focus:outline-none
            transition-shadow resize-none
            ' . ($error ? 'ring-red-500 focus:ring-red-500' : '')
        ]) }}
    >{{ $slot }}</textarea>

    @if($error)
    <p class="mt-1.5 text-xs text-red-500">{{ $error }}</p>
    @elseif($hint)
    <p class="mt-1.5 text-xs text-surface-500">{{ $hint }}</p>
    @endif
</div>
