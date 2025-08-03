@extends('layouts.app')

@section('title', __('Salesmen'))

@section('content')
<!-- Modern Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h2 mb-2 fw-bold d-flex align-items-center">
            <div class="p-2 rounded-circle bg-primary bg-opacity-10 me-3">
                <i class="bi bi-people text-primary"></i>
            </div>
            {{ __('Salesmen') }}
        </h1>
        <p class="text-muted mb-0 ms-5 ps-2">{{ __('Manage sales team members and assignments') }}</p>
    </div>
    <div class="d-flex gap-2" style="margin-top: 0.5rem;">
        <!-- Import/Export Button Group -->
        <div class="btn-group me-2" role="group">
            <button type="button" class="btn btn-outline-success shadow-sm" onclick="exportSalesmen()" style="border-radius: 8px 0 0 8px;">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadSalesmenModal">
                <i class="bi bi-upload me-1"></i>Import
            </button>
            <a href="{{ route('salesmen.template') }}" class="btn btn-outline-info shadow-sm" style="border-radius: 0 8px 8px 0;">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Template
            </a>
        </div>
        <a href="{{ route('salesmen.create') }}" class="btn btn-primary shadow-sm" style="border-radius: 8px;">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Add Salesman') }}
        </a>
    </div>
</div>

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

<!-- Modern Salesmen Card -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center" style="border-radius: 12px 12px 0 0;">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3 fw-semibold d-flex align-items-center">
                <div class="p-2 rounded-circle bg-success bg-opacity-10 me-3">
                    <i class="bi bi-people text-success"></i>
                </div>
                {{ __('Salesmen List') }}
            </h5>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                {{ count($salesmen) }} {{ __('records') }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form method="GET" action="{{ route('salesmen.index') }}" class="d-flex gap-2">
                <div class="input-group shadow-sm" style="width: 280px; border-radius: 8px;">
                    <span class="input-group-text bg-light border-0" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0" 
                           placeholder="{{ __('Search salesmen...') }}" value="{{ request('search') }}"
                           style="border-radius: 0 8px 8px 0;">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('salesmen.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                @endif
            </form> 
                       style="border-radius: 0 8px 8px 0; box-shadow: none;">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="salesmenTable">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 px-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-badge me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'employee_code', 'direction' => request('sort') === 'employee_code' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Employee Code') }}
                                    @if(request('sort') === 'employee_code')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person me-2 text-muted"></i>
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
                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'region_name', 'direction' => request('sort') === 'region_name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Region') }}
                                    @if(request('sort') === 'region_name')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-diagram-3 me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'channel_name', 'direction' => request('sort') === 'channel_name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Channel') }}
                                    @if(request('sort') === 'channel_name')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-tags me-2 text-muted"></i>{{ __('Classification') }}
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
                        <th class="border-0 text-center" style="width: 120px;">
                            <i class="bi bi-gear me-1 text-muted"></i>{{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesmen as $salesman)
                        <tr>
                            <td class="px-4">
                                <code class="bg-primary-subtle text-primary px-2 py-1 rounded small">{{ $salesman->salesman_code }}</code>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $salesman->name }}</div>
                            </td>
                            <td>
                                @if($salesman->region)
                                    <div class="text-muted small">{{ $salesman->region->name }}</div>
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-dash-circle me-1"></i>{{ __('N/A') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($salesman->channel)
                                    <div class="text-muted small">{{ $salesman->channel->name }}</div>
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-dash-circle me-1"></i>{{ __('N/A') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $classifications = $salesman->getClassificationListAttribute();
                                @endphp
                                @if(!empty($classifications))
                                    @foreach($classifications as $classification)
                                        @if($classification === 'food')
                                            <span class="badge bg-success-subtle text-success px-2 me-1">
                                                <i class="bi bi-cup-hot me-1"></i>{{ __('Food') }}
                                            </span>
                                        @elseif($classification === 'non_food')
                                            <span class="badge bg-info-subtle text-info px-2 me-1">
                                                <i class="bi bi-box me-1"></i>{{ __('Non-Food') }}
                                            </span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2">
                                        <i class="bi bi-question-circle me-1"></i>{{ __('Unknown') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($salesman->is_active)
                                    <span class="badge bg-success-subtle text-success px-2">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2">
                                        <i class="bi bi-pause-circle me-1"></i>{{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('salesmen.show', $salesman) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="{{ __('View') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('salesmen.edit', $salesman) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="{{ __('Edit') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('salesmen.toggle_status', $salesman) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm {{ $salesman->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                title="{{ $salesman->is_active ? __('Deactivate') : __('Activate') }}"
                                                data-bs-toggle="tooltip">
                                            <i class="bi {{ $salesman->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('salesmen.destroy', $salesman) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('{{ __('Are you sure?') }}')" 
                                                title="{{ __('Delete') }}"
                                                data-bs-toggle="tooltip">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">{{ __('No salesmen found') }}</p>
                                    <small class="text-muted">{{ __('Try adjusting your search or add a new salesman') }}</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#salesmenTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Salesmen Import/Export Functions
function showAlert(message, type = 'danger') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
}

function exportSalesmen() {
    window.location.href = "{{ route('salesmen.export') }}";
}

function toggleUpdateOption() {
    const fileInput = document.getElementById('salesmenFileInput');
    const updateCheckbox = document.getElementById('updateExistingSalesmen');
    updateCheckbox.disabled = !fileInput.files.length;
}

function uploadSalesmen() {
    const fileInput = document.getElementById('salesmenFileInput');
    const updateExisting = document.getElementById('updateExistingSalesmen').checked;
    
    if (!fileInput.files.length) {
        showAlert('Please select a file to upload.');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('update_existing', updateExisting ? '1' : '0');
    formData.append('_token', '{{ csrf_token() }}');
    
    const submitBtn = document.querySelector('#uploadSalesmenModal .btn-primary');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Uploading...';
    
    fetch("{{ route('salesmen.import') }}", {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Close modal
            document.querySelector('#uploadSalesmenModal .btn-close').click();
            // Reload page to show new data
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Import failed.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred during import.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<!-- Upload Salesmen Modal -->
<div class="modal fade" id="uploadSalesmenModal" tabindex="-1" aria-labelledby="uploadSalesmenModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="uploadSalesmenModalLabel">Upload Salesmen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="salesmenFileInput" class="form-label">Select Excel File</label>
          <input type="file" class="form-control" id="salesmenFileInput" accept=".xlsx,.xls" onchange="toggleUpdateOption()">
          <div class="form-text">Upload an Excel file with salesmen data. Use the template for proper format.</div>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="updateExistingSalesmen" disabled>
          <label class="form-check-label" for="updateExistingSalesmen">
            Update existing salesmen (match by salesman code)
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="uploadSalesmen()">Upload</button>
      </div>
    </div>
  </div>
</div>

@endpush
@endsection 