@extends('layouts.app')

@section('title', __('Salesmen'))

@section('content')

<!-- Page Header -->
<x-page_header 
    title="{{ __('Salesmen') }}"
    description="{{ __('Manage sales team members and assignments') }}"
    icon="bi-people">
    
    <x-slot name="actions">
        <x-crud_actions 
            :hasExport="true"
            :hasImport="true" 
            :hasTemplate="true"
            exportOnclick="exportSalesmen()"
            importOnclick="showUploadModal()"
            templateHref="{{ route('salesmen.template') }}"
            addButtonText="{{ __('Add Salesman') }}"
            addButtonHref="{{ route('salesmen.create') }}"
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
    title="{{ __('Salesmen List') }}"
    icon="bi-people"
    search-placeholder="{{ __('Search salesmen...') }}"
    :search-value="request('search')"
    search-route="{{ route('salesmen.index') }}"
    :total-records="method_exists($salesmen, 'total') ? $salesmen->total() : count($salesmen)"
    :columns="[
        ['label' => __('Salesman Code'), 'icon' => 'bi-hash', 'sortable' => true],
        ['label' => __('Name'), 'icon' => 'bi-person', 'sortable' => true],
        ['label' => __('Region'), 'icon' => 'bi-geo-alt'],
        ['label' => __('Phone'), 'icon' => 'bi-telephone'],
        ['label' => __('Status'), 'icon' => 'bi-activity', 'class' => 'text-center', 'align' => 'justify-content-center', 'sortable' => true],
        ['label' => __('Actions'), 'icon' => 'bi-gear', 'class' => 'text-center', 'align' => 'justify-content-center']
    ]"
>
    @forelse($salesmen as $salesman)
        <tr>
            <td>
                <code class="bg-primary-subtle text-primary px-2 py-1 rounded small">{{ $salesman->salesman_code }}</code>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-person text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-medium text-dark">{{ $salesman->name }}</div>
                        @if($salesman->email)
                            <small class="text-muted">{{ $salesman->email }}</small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                @if($salesman->region)
                    <span class="badge bg-secondary-subtle text-secondary">{{ $salesman->region->region_name ?? $salesman->region_name }}</span>
                @else
                    <span class="text-muted">{{ __('No region assigned') }}</span>
                @endif
            </td>
            <td>
                @if($salesman->phone)
                    <span class="text-muted">{{ $salesman->phone }}</span>
                @else
                    <span class="text-muted">{{ __('No phone') }}</span>
                @endif
            </td>
            <td class="text-center">
                @if($salesman->is_active ?? true)
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                    </span>
                @else
                    <span class="badge bg-secondary">
                        <i class="bi bi-pause-circle me-1"></i>{{ __('Inactive') }}
                    </span>
                @endif
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <a href="{{ route('salesmen.show', $salesman) }}" 
                       class="btn btn-outline-primary btn-sm"
                       title="{{ __('View') }}">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('salesmen.edit', $salesman) }}" 
                       class="btn btn-outline-warning btn-sm"
                       title="{{ __('Edit') }}">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if(method_exists($salesman, 'toggleStatus'))
                        <form action="{{ route('salesmen.toggle_status', $salesman) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-sm {{ ($salesman->is_active ?? true) ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                    title="{{ ($salesman->is_active ?? true) ? __('Deactivate') : __('Activate') }}">
                                <i class="bi {{ ($salesman->is_active ?? true) ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                            </button>
                        </form>
                    @endif
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="confirmDelete('{{ $salesman->id }}', '{{ $salesman->name }}')"
                            title="{{ __('Delete') }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                
                <!-- Hidden Delete Form -->
                <form id="delete-form-{{ $salesman->id }}" 
                      action="{{ route('salesmen.destroy', $salesman) }}" 
                      method="POST" 
                      class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-people display-4 mb-3"></i>
                    <p class="mb-0">{{ __('No salesmen found') }}</p>
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
                {{ __('Showing') }} {{ method_exists($salesmen, 'firstItem') ? $salesmen->firstItem() ?? 0 : 1 }} {{ __('to') }} {{ method_exists($salesmen, 'lastItem') ? $salesmen->lastItem() ?? 0 : count($salesmen) }} 
                {{ __('of') }} {{ method_exists($salesmen, 'total') ? $salesmen->total() : count($salesmen) }} {{ __('results') }}
            </div>
            @if(method_exists($salesmen, 'links'))
                <div>
                    {{ $salesmen->links() }}
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
                    <i class="bi bi-cloud-upload me-2"></i>{{ __('Import Salesmen') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('salesmen.import') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
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
                <p>{{ __('Are you sure you want to delete the salesman') }} "<span id="deleteSalesmanName"></span>"?</p>
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
let deleteSalesmanId = null;

function showUploadModal() {
    const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
    uploadModal.show();
}

function confirmDelete(salesmanId, salesmanName) {
    deleteSalesmanId = salesmanId;
    document.getElementById('deleteSalesmanName').textContent = salesmanName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteSalesmanId) {
        document.getElementById('delete-form-' + deleteSalesmanId).submit();
    }
});

function exportSalesmen() {
    const searchParams = new URLSearchParams(window.location.search);
    const exportUrl = new URL('{{ route("salesmen.export") }}', window.location.origin);
    
    // Add current filters to export
    if (searchParams.get('search')) {
        exportUrl.searchParams.set('search', searchParams.get('search'));
    }
    
    window.location.href = exportUrl.toString();
}

// Upload form handling
document.getElementById('uploadForm').addEventListener('submit', function() {
    const uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("Uploading...") }}';
    uploadBtn.disabled = true;
});
</script>
@endpush
