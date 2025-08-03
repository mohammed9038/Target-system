@props([
    'type' => 'info',
    'dismissible' => true,
    'icon' => null
])

@php
    $icons = [
        'success' => 'bi-check-circle',
        'danger' => 'bi-exclamation-triangle',
        'warning' => 'bi-exclamation-triangle',
        'info' => 'bi-info-circle'
    ];
    
    $defaultIcon = $icons[$type] ?? 'bi-info-circle';
    $alertIcon = $icon ?? $defaultIcon;
@endphp

<div class="alert alert-{{ $type }} {{ $dismissible ? 'alert-dismissible fade show' : '' }}" role="alert">
    <div class="d-flex align-items-start">
        <i class="{{ $alertIcon }} me-2 mt-1"></i>
        <div class="flex-grow-1">
            {{ $slot }}
        </div>
    </div>
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
