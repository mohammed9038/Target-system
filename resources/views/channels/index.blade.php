@extends('layouts.app')

@section('title', __('Channels'))

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h2 mb-1">{{ __('Channels') }}</h1>
        <p class="text-muted mb-0">{{ __('Manage sales channels') }}</p>
    </div>
    <div class="d-flex gap-2" style="margin-top: 0.5rem;">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportChannels()">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ __('Export') }}
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showUploadModal()">
                <i class="bi bi-upload me-1"></i>{{ __('Import') }}
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                <i class="bi bi-download me-1"></i>{{ __('Template') }}
            </button>
        </div>
        <a href="{{ route('channels.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Add Channel') }}
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

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 me-3">
                    <i class="bi bi-diagram-3 me-2"></i>{{ __('Channels List') }}
                </h5>
                <small class="text-muted">
                    {{ count($channels) }} {{ __('records') }}
                </small>
            </div>
            <div class="input-group" style="width: 250px;">
                <form method="GET" action="{{ route('channels.index') }}" class="d-flex gap-2">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="{{ __('Search channels...') }}" value="{{ request('search') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                    </div>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('channels.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="channelsTable">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 px-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-hash me-2 text-muted"></i>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('sort') === 'id' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Channel Code') }}
                                    @if(request('sort') === 'id')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-diagram-3 me-2 text-muted"></i>
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
                        <th class="border-0 text-center" style="width: 120px;">
                            <i class="bi bi-gear me-1 text-muted"></i>{{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($channels as $channel)
                        <tr>
                            <td class="px-4">
                                <code class="bg-info-subtle text-info px-2 py-1 rounded small">{{ $channel->channel_code }}</code>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $channel->name }}</div>
                            </td>
                            <td>
                                @if($channel->is_active)
                                    <span class="badge bg-success-subtle text-success px-2">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2">
                                        <i class="bi bi-x-circle me-1"></i>{{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('channels.show', $channel) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="{{ __('View') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('channels.edit', $channel) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="{{ __('Edit') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('channels.toggle_status', $channel) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm {{ $channel->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                title="{{ $channel->is_active ? __('Deactivate') : __('Activate') }}"
                                                data-bs-toggle="tooltip">
                                            <i class="bi {{ $channel->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('channels.destroy', $channel) }}" method="POST" class="d-inline">
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
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-diagram-3" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">{{ __('No channels found') }}</p>
                                    <small class="text-muted">{{ __('Try adjusting your search or create a new channel') }}</small>
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
    const rows = document.querySelectorAll('#channelsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Import/Export functions
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
    
    const content = document.querySelector('.container-fluid');
    content.insertBefore(alertDiv, content.firstChild);
    
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function exportChannels() {
    const url = new URL('{{ route("channels.export") }}', window.location.origin);
    const params = new URLSearchParams();
    params.append('format', 'xlsx');
    url.search = params.toString();
    window.location.href = url.toString();
}

function showUploadModal() {
    new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

function downloadTemplate() {
    window.location.href = '{{ route("channels.template") }}';
}

async function uploadChannels() {
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
        const response = await fetch('{{ route("channels.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            showAlert("Import completed successfully!", "success");
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
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

<!-- Import Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Channels') }}</h5>
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
                            <div class="form-text">{{ __('If checked, existing channels will be updated. Otherwise, duplicates will be skipped.') }}</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="uploadChannels()">{{ __('Import') }}</button>
            </div>
        </div>
    </div>
</div>

@endpush
@endsection 