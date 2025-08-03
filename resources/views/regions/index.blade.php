@extends('layouts.app')

@section('title', __('Regions'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">{{ __('Regions') }}</h1>
        <p class="text-muted mb-0">{{ __('Manage sales regions and territories') }}</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportRegions()">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ __('Export') }}
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showUploadModal()">
                <i class="bi bi-upload me-1"></i>{{ __('Import') }}
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                <i class="bi bi-download me-1"></i>{{ __('Template') }}
            </button>
        </div>
        <a href="{{ route('regions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Add Region') }}
        </a>
    </div>
</div>

<!-- Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle me-2"></i>
            <div>
                <strong>{{ __('Success!') }}</strong> {{ session('success') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <div>
                <strong>{{ __('Error!') }}</strong> {{ session('error') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Regions Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 me-3">
                    <i class="bi bi-geo-alt me-2"></i>{{ __('Regions List') }}
                </h5>
                <small class="text-muted" id="resultsCount">
                    {{ method_exists($regions, 'total') ? $regions->total() : count($regions) }} {{ __('records') }}
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('regions.index') }}" class="d-flex gap-2">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="{{ __('Search regions...') }}" value="{{ request('search') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                    </div>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('regions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="regionsTable">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 px-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-hash me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('sort') === 'id' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Region Code') }}
                                    @if(request('sort') === 'id')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Name') }}
                                    @if(request('sort') === 'name' || !request('sort'))
                                        <i class="bi bi-chevron-{{ (request('direction') === 'asc' || !request('direction')) ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-activity me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => request('sort') === 'is_active' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Status') }}
                                    @if(request('sort') === 'is_active')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Created') }}
                                    @if(request('sort') === 'created_at')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0 text-center" style="width: 120px;">
                            <i class="bi bi-gear me-1 text-muted"></i>{{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($regions as $region)
                        <tr class="region-row" data-status="{{ $region->is_active ? 'active' : 'inactive' }}">
                            <td class="px-4">
                                <code class="bg-primary-subtle text-primary px-2 py-1 rounded small">{{ $region->region_code }}</code>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $region->name }}</div>
                            </td>
                            <td>
                                @if($region->is_active)
                                    <span class="badge bg-success-subtle text-success px-2">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2">
                                        <i class="bi bi-pause-circle me-1"></i>{{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $region->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('regions.show', $region) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="{{ __('View') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('regions.edit', $region) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="{{ __('Edit') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('regions.toggle_status', $region) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm {{ $region->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                title="{{ $region->is_active ? __('Deactivate') : __('Activate') }}"
                                                data-bs-toggle="tooltip">
                                            <i class="bi {{ $region->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete('{{ $region->id }}', '{{ $region->name }}')"
                                            title="{{ __('Delete') }}"
                                            data-bs-toggle="tooltip">
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
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-geo-alt" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">{{ __('No regions found') }}</p>
                                    <small class="text-muted">{{ __('Try adjusting your search or create a new region') }}</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(method_exists($regions, 'hasPages') && $regions->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    {{ __('Showing') }} {{ $regions->firstItem() }} {{ __('to') }} {{ $regions->lastItem() }} {{ __('of') }} {{ $regions->total() }} {{ __('results') }}
                </div>
                {{ $regions->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>{{ __('Confirm Delete') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete the region') }} <strong id="regionName"></strong>?</p>
                <p class="text-muted small mb-0">{{ __('This action cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bi bi-trash me-2"></i>{{ __('Delete Region') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const clearFilters = document.getElementById('clearFilters');
    const resultsCount = document.getElementById('resultsCount');
    const table = document.getElementById('regionsTable');
    const rows = table.querySelectorAll('.region-row');

    // Search functionality
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilterValue = statusFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const code = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const status = row.getAttribute('data-status');
            
            const matchesSearch = name.includes(searchTerm) || code.includes(searchTerm);
            const matchesStatus = !statusFilterValue || status === statusFilterValue;
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update results count
        resultsCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'region' : 'regions'} found`;
    }

    // Event listeners
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    
    clearFilters.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        filterTable();
    });

    // Initialize
    filterTable();
});

// Delete confirmation
function confirmDelete(regionId, regionName) {
    document.getElementById('regionName').textContent = regionName;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
    
    document.getElementById('confirmDelete').onclick = function() {
        document.getElementById(`delete-form-${regionId}`).submit();
    };
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

<style>
.badge.bg-success-subtle {
    background-color: rgba(16, 185, 129, 0.1) !important;
}

.badge.bg-secondary-subtle {
    background-color: rgba(100, 116, 139, 0.1) !important;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(79, 70, 229, 0.05);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.modal-content {
    border-radius: 0.75rem;
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.pagination {
    gap: 0.25rem;
}

.page-link {
    border-radius: 0.375rem;
    border: 1px solid var(--border-color);
    color: var(--text-primary);
}

.page-link:hover {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
</style>

<!-- Import Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Regions') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="upload_file" class="form-label">{{ __('Select Excel File') }}</label>
                        <input type="file" class="form-control" id="upload_file" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">{{ __('Supported formats: Excel (.xlsx, .xls) and CSV (.csv)') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_existing" name="update_existing" value="1">
                            <label class="form-check-label" for="update_existing">
                                {{ __('Update existing records') }}
                            </label>
                            <div class="form-text">{{ __('If checked, existing regions will be updated. Otherwise, duplicates will be skipped.') }}</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="uploadRegions()">{{ __('Import') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
// Alert function
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the content
    const content = document.querySelector('.container-fluid');
    content.insertBefore(alertDiv, content.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Export function
function exportRegions() {
    const url = new URL('{{ route("regions.export") }}', window.location.origin);
    
    // Add any filters if needed
    const params = new URLSearchParams();
    params.append('format', 'xlsx');
    
    url.search = params.toString();
    window.location.href = url.toString();
}

// Show upload modal
function showUploadModal() {
    new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

// Download template
function downloadTemplate() {
    window.location.href = '{{ route("regions.template") }}';
}

// Upload function
async function uploadRegions() {
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('upload_file');
    const updateExisting = document.getElementById('update_existing').checked;
    
    if (!fileInput.files.length) {
        showAlert("Please select a file to upload.", "warning");
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('update_existing', updateExisting ? '1' : '0');
    formData.append('_token', '{{ csrf_token() }}');
    
    try {
        const response = await fetch('{{ route("regions.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.text();
        
        if (response.ok) {
            showAlert("Import completed successfully!", "success");
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
            // Reload the page to show updated data
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert("Import failed. Please check your file format.", "danger");
        }
    } catch (error) {
        console.error('Upload error:', error);
        showAlert("An error occurred during import.", "danger");
    }
}
</script>

@endsection 