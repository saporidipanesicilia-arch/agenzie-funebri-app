@props([
    'variant' => 'primary', // primary, secondary, danger, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'fullWidth' => false
])

@php
    $baseClass = "inline-flex items-center justify-center font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg";
    
    $variants = [
        'primary' => 'bg-funeral-900 text-white hover:bg-funeral-800 focus:ring-funeral-900 border border-transparent shadow-sm',
        'secondary' => 'bg-white text-funeral-700 border border-funeral-200 hover:bg-funeral-50 hover:text-funeral-900 focus:ring-funeral-500 shadow-sm',
        'danger' => 'bg-status-error-bg text-status-error-text border border-transparent hover:bg-rose-100 focus:ring-rose-500',
        'ghost' => 'bg-transparent text-funeral-600 hover:text-funeral-900 hover:bg-funeral-100 border border-transparent',
    ];

    $sizes = [
        'sm' => 'px-3 py-2 text-xs', // Increased from py-1.5
        'md' => 'px-5 py-2.5 text-sm', // Increased from px-4 py-2 (~40px height) to ~44px
        'lg' => 'px-6 py-3.5 text-base', // Increased to ~52px
        'xl' => 'px-8 py-4 text-lg', // New size
    ];

    $widthClass = $fullWidth ? 'w-full' : '';
    $classes = "$baseClass {$variants[$variant]} {$sizes[$size]} $widthClass";
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <span class="mr-2 -ml-1">
            {{ $icon }}
        </span>
    @endif
    {{ $slot }}
</button>
