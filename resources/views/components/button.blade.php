@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'loading' => false,
    'href' => null
])

@php
    $baseClasses = 'btn';
    $variantClass = "btn-{$variant}";
    $sizeClass = $size !== 'md' ? "btn-{$size}" : '';
    
    $classes = trim("{$baseClasses} {$variantClass} {$sizeClass}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
        @elseif($icon)
            <i class="{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }} {{ $loading ? 'disabled' : '' }}>
        @if($loading)
            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
        @elseif($icon)
            <i class="{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </button>
@endif
