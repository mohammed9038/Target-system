@extends('layouts.app')

@section('title', __('Regions'))

@section('content')

<!-- Page Header -->
<x-page_header 
    title="{{ __('Regions') }}"
    description="{{ __('Manage sales regions and territories') }}"
    icon="bi-geo-alt">
    
    <x-slot name="actions">
        <x-crud_actions 
            :hasExport="true"
            :hasImport="true" 
            :hasTemplate="true"
            exportOnclick="exportRegions()"
            importOnclick="showUploadModal()"
            templateHref="{{ route('regions.template') }}"
            addButtonText="{{ __('Add Region') }}"
            addButtonHref="{{ route('regions.create') }}"
        />
    </x-slot>
</x-page_header>

<!-- Flash Messages -->
@if(session('success'))
    <x-alert type="success" class="mb-4">
        <strong>{{ __('Success!') }}</strong> {{ session('success') }}
    </x-alert>
@endif

@if(session('error'))
    <x-alert type="danger" class="mb-4">
        <strong>{{ __('Error!') }}</strong> {{ session('error') }}
    </x-alert>
@endif

<!-- Data Table -->
<x-data_table
    title="{{ __('Regions List') }}"
    icon="bi-geo-alt"
    search-placeholder="{{ __('Search regions...') }}"
    :search-value="request('search')"
    search-route="{{ route('regions.index') }}"
    :total-records="method_exists($regions, 'total') ? $regions->total() : count($regions)"
    :columns="[
        ['label' => __('Region Code'), 'icon' => 'bi-hash', 'sortable' => true],
        ['label' => __('Name'), 'icon' => 'bi-geo-alt', 'sortable' => true],
        ['label' => __('Status'), 'icon' => 'bi-activity', 'class' => 'text-center', 'align' => 'justify-content-center', 'sortable' => true],
        ['label' => __('Created'), 'icon' => 'bi-calendar', 'sortable' => true],
        ['label' => __('Actions'), 'icon' => 'bi-gear', 'class' => 'text-center', 'align' => 'justify-content-center']
    ]"
>
    @forelse($regions as $region)
        <tr class="region-row" data-status="{{ $region->is_active ? 'active' : 'inactive' }}">
            <td>
                <code class="bg-primary-subtle text-primary px-2 py-1 rounded small">{{ $region->region_code }}</code>
            </td>
            <td>
                <div class="fw-medium text-dark">{{ $region->name }}</div>
            </td>
            <td class="text-center">
                @if($region->is_active)
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                    </span>
                @else
                    <span class="badge bg-secondary">
                        <i class="bi bi-pause-circle me-1"></i>{{ __('Inactive') }}
                    </span>
                @endif
            </td>
            <td>
                <span class="text-muted small">{{ $region->created_at->format('M d, Y') }}</span>
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <a href="{{ route('regions.show', $region) }}" 
                       class="btn btn-outline-primary btn-sm"
                       title="{{ __('View') }}">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('regions.edit', $region) }}" 
                       class="btn btn-outline-warning btn-sm"
                       title="{{ __('Edit') }}">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('regions.toggle_status', $region) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" 
                                class="btn btn-sm {{ $region->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                title="{{ $region->is_active ? __('Deactivate') : __('Activate') }}">
                            <i class="bi {{ $region->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                        </button>
                    </form>
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="confirmDelete('{{ $region->id }}', '{{ $region->name }}')"
                            title="{{ __('Delete') }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                
                <!-- Hidden Delete Form -->
                <form id="delete-form-{{ $region->id }}" 
                      action="{{ route('regions.destroy', $region) }}" 
                      method="POST" 
                      class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-geo-alt display-4 mb-3"></i>
                    <p class="mb-0">{{ __('No regions found') }}</p>
                    @if(request('search'))
                        <small>{{ __('Try adjusting your search terms') }}</small>
                    @endif
                </div>
            </td>
        </tr>
    @endforelse
    
    <x-slot name="actions">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                {{ __('Showing') }} {{ method_exists($regions, 'firstItem') ? $regions->firstItem() ?? 0 : 1 }} {{ __('to') }} {{ method_exists($regions, 'lastItem') ? $regions->lastItem() ?? 0 : count($regions) }} 
                {{ __('of') }} {{ method_exists($regions, 'total') ? $regions->total() : count($regions) }} {{ __('results') }}
            </div>
            @if(method_exists($regions, 'links'))
                <div>
                    {{ $regions->links() }}
                </div>
            @endif
        </div>
    </x-slot>
</x-data_table>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="bi bi-cloud-upload me-2"></i>{{ __('Import Regions') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('regions.import') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">{{ __('Select Excel file') }}</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">{{ __('Supported formats: .xlsx, .xls, .csv') }}</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('Make sure your Excel file has the correct column headers. Download the template for reference.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="bi bi-upload me-2"></i>{{ __('Upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>{{ __('Confirm Delete') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete the region') }} "<span id="deleteRegionName"></span>"?</p>
                <p class="text-muted small">{{ __('This action cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-2"></i>{{ __('Delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let deleteRegionId = null;

function showUploadModal() {
    const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
    uploadModal.show();
}

function confirmDelete(regionId, regionName) {
    deleteRegionId = regionId;
    document.getElementById('deleteRegionName').textContent = regionName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteRegionId) {
        document.getElementById('delete-form-' + deleteRegionId).submit();
    }
});

function exportRegions() {
    const searchParams = new URLSearchParams(window.location.search);
    const exportUrl = new URL('{{ route("regions.export") }}', window.location.origin);
    
    // Add current filters to export
    if (searchParams.get('search')) {
        exportUrl.searchParams.set('search', searchParams.get('search'));
    }
    if (searchParams.get('status')) {
        exportUrl.searchParams.set('status', searchParams.get('status'));
    }
    
    window.location.href = exportUrl.toString();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Upload form handling
document.getElementById('uploadForm').addEventListener('submit', function() {
    const uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("Uploading...") }}';
    uploadBtn.disabled = true;
});
</script>
@endpush
