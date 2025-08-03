@props([
    'hasExport' => true,
    'hasImport' => true,
    'hasTemplate' => true,
    'exportOnclick' => '',
    'importOnclick' => '',
    'templateHref' => '',
    'addButtonText' => 'Add',
    'addButtonHref' => '',
    'addButtonIcon' => 'bi-plus-circle'
])

<div class="d-flex gap-2 align-items-center">
    @if($hasExport || $hasImport || $hasTemplate)
        <div class="btn-group" role="group">
            @if($hasExport)
                <x-button variant="outline-success" size="sm" icon="bi-download" onclick="{{ $exportOnclick }}">
                    {{ __('Export') }}
                </x-button>
            @endif
            
            @if($hasImport)
                <x-button variant="outline-primary" size="sm" icon="bi-upload" onclick="{{ $importOnclick }}">
                    {{ __('Import') }}
                </x-button>
            @endif
            
            @if($hasTemplate)
                <x-button variant="outline-info" size="sm" icon="bi-file-earmark-spreadsheet" href="{{ $templateHref }}">
                    {{ __('Template') }}
                </x-button>
            @endif
        </div>
    @endif
    
    @if($addButtonHref)
        <x-button variant="primary" size="sm" icon="{{ $addButtonIcon }}" href="{{ $addButtonHref }}">
            {{ $addButtonText }}
        </x-button>
    @endif
</div>
