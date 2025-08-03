@extends('layouts.app')

@section('title', __('Categories'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">{{ __('Categories') }}</h1>
        <p class="text-muted mb-0">{{ __('Manage product categories') }}</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportCategories()">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ __('Export') }}
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showUploadModal()">
                <i class="bi bi-upload me-1"></i>{{ __('Import') }}
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                <i class="bi bi-download me-1"></i>{{ __('Template') }}
            </button>
        </div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Add Category') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <strong>{{ __('Success!') }}</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>{{ __('Error!') }}</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-tags me-2"></i>{{ __('Categories List') }}
            </h5>
            <form method="GET" action="{{ route('categories.index') }}" class="d-flex gap-2">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="{{ __('Search categories...') }}" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body">
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'category_code', 'direction' => request('sort') === 'category_code' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Category Code') }}
                                    @if(request('sort') === 'category_code')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Name') }}
                                    @if(request('sort') === 'name' || !request('sort'))
                                        <i class="bi bi-chevron-{{ (request('direction') === 'asc' || !request('direction')) ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'supplier_name', 'direction' => request('sort') === 'supplier_name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Supplier') }}
                                    @if(request('sort') === 'supplier_name')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => request('sort') === 'is_active' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Status') }}
                                    @if(request('sort') === 'is_active')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $category->category_code }}</span></td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->supplier->name ?? 'N/A' }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('categories.show', $category) }}" class="btn btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('categories.toggle_status', $category) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                    title="{{ $category->is_active ? __('Deactivate') : __('Activate') }}">
                                                <i class="bi {{ $category->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('{{ __('Are you sure?') }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    {{ __('Showing') }} {{ $categories->firstItem() }} {{ __('to') }} {{ $categories->lastItem() }} 
                    {{ __('of') }} {{ $categories->total() }} {{ __('results') }}
                </div>
                <div>
                    {{ $categories->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
                </div>
                <h5 class="text-muted mb-3">{{ __('No categories found') }}</h5>
                <p class="text-muted mb-4">{{ __('Start by adding your first category') }}</p>
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('Add Category') }}
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Categories') }}</h5>
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
                            <div class="form-text">{{ __('If checked, existing categories will be updated. Otherwise, duplicates will be skipped.') }}</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="uploadCategories()">{{ __('Import') }}</button>
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
    const content = document.querySelector('main .container-fluid') || document.querySelector('.container');
    if (content) {
        content.insertBefore(alertDiv, content.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

// Export function
function exportCategories() {
    const url = new URL('{{ route("categories.export") }}', window.location.origin);
    
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
    window.location.href = '{{ route("categories.template") }}';
}

// Upload function
async function uploadCategories() {
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
        const response = await fetch('{{ route("categories.import") }}', {
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
