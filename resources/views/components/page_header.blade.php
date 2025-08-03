@props([
    'title' => '',
    'description' => '',
    'icon' => 'bi-circle',
    'actions' => null
])

<div class="page-header">
    <div class="page-header-content">
        <h1 class="d-flex align-items-center gap-2 mb-1">
            @if($icon)
                <i class="{{ $icon }} text-primary" style="font-size: 1.4rem;"></i>
            @endif
            {{ $title }}
        </h1>
        @if($description)
            <p class="page-header-description text-muted mb-0">{{ $description }}</p>
        @endif
    </div>
    
    @if($actions)
        <div class="page-header-actions">
            {{ $actions }}
        </div>
    @endif
</div>
