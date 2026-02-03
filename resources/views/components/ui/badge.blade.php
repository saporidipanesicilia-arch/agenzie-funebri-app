@props([
    'status' => 'info', // success, warning, error, info, neutral
    'label' => '',
])

@php
    $baseClass = "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset";

    // Styles mapped to Tailwind theme variables defined in app.css
    $styles = [
        'success' => 'bg-status-success-bg text-status-success-text ring-status-success-text/20',
        'warning' => 'bg-status-warning-bg text-status-warning-text ring-status-warning-text/20',
        'error' => 'bg-status-error-bg text-status-error-text ring-status-error-text/20',
        'info' => 'bg-status-info-bg text-status-info-text ring-status-info-text/20',
        'neutral' => 'bg-funeral-100 text-funeral-600 ring-funeral-500/10',
    ];

    $styleClass = $styles[$status] ?? $styles['neutral'];
@endphp

<span {{ $attributes->merge(['class' => "$baseClass $styleClass"]) }}>
    {{ $label ?? $slot }}
</span>
