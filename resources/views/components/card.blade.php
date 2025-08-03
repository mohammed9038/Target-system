@props([
    'title' => null,
    'icon' => null,
    'actions' => null,
    'headerClass' => '',
    'bodyClass' => ''
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || $actions)
        <div class="card-header {{ $headerClass }}">
            <div class="d-flex justify-content-between align-items-center">
                @if($title)
                    <h5 class="card-title">
                        @if($icon)
                            <i class="{{ $icon }}"></i>
                        @endif
                        {{ $title }}
                    </h5>
                @endif
                
                @if($actions)
                    <div class="card-actions">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
