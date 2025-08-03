@extends('layouts.app')

@section('title', __('Suppliers'))

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-building me-2"></i>{{ __('Suppliers') }}
        </h1>
        <p class="text-muted mb-0">{{ __('Manage your supplier database') }}</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group">
            <a href="{{ route('suppliers.export') }}" class="btn btn-outline-success">
                <i class="bi bi-download me-1"></i>Export
            </a>
            <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-upload me-1"></i>Import
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('suppliers.import.form') }}">
                    <i class="bi bi-file-earmark-excel me-1"></i>Import Suppliers
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('suppliers.template') }}">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Template
                </a></li>
            </ul>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('Add Supplier') }}
        </a>
    </div>
</div>

<!-- Success Alert -->
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

<!-- Error Alert -->
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

<!-- Suppliers Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">
                <i class="bi bi-building me-2"></i>{{ __('Suppliers List') }}
            </h5>
            <small class="text-muted">{{ $suppliers->total() }} {{ __('suppliers found') }}</small>
        </div>
        
        <!-- Search Form -->
        <form method="GET" action="{{ route('suppliers.index') }}" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" 
                   placeholder="{{ __('Search suppliers...') }}" 
                   value="{{ request('search') }}" style="width: 250px;">
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-search"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </form>
    </div>
    
    <div class="card-body p-0">
        @if($suppliers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="120">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'supplier_code', 'direction' => request('sort') === 'supplier_code' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Code') }}
                                    @if(request('sort') === 'supplier_code')
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
                            <th width="120">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'phone', 'direction' => request('sort') === 'phone' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Phone') }}
                                    @if(request('sort') === 'phone')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="150">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => request('sort') === 'email' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Email') }}
                                    @if(request('sort') === 'email')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="120">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'city', 'direction' => request('sort') === 'city' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('City') }}
                                    @if(request('sort') === 'city')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="100">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => request('sort') === 'is_active' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ __('Status') }}
                                    @if(request('sort') === 'is_active')
                                        <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="150" class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $supplier->supplier_code }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $supplier->name }}</div>
                                            @if($supplier->company_name && $supplier->company_name !== $supplier->name)
                                                <small class="text-muted">{{ $supplier->company_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($supplier->phone)
                                        <a href="tel:{{ $supplier->phone }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $supplier->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ __('No phone') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i>{{ Str::limit($supplier->email, 20) }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ __('No email') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->city)
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $supplier->city }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ __('No city') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-x-circle me-1"></i>{{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('suppliers.show', $supplier) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="{{ __('View') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('suppliers.toggle_status', $supplier) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $supplier->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                    title="{{ $supplier->is_active ? __('Deactivate') : __('Activate') }}">
                                                <i class="bi {{ $supplier->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this supplier?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="{{ __('Delete') }}">
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
            
            <!-- Pagination -->
            @if($suppliers->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            {{ __('Showing') }} {{ $suppliers->firstItem() }} {{ __('to') }} {{ $suppliers->lastItem() }} 
                            {{ __('of') }} {{ $suppliers->total() }} {{ __('results') }}
                        </div>
                        {{ $suppliers->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-building display-1 text-muted"></i>
                </div>
                <h5 class="text-muted mb-3">{{ __('No Suppliers Found') }}</h5>
                @if(request('search'))
                    <p class="text-muted mb-3">
                        {{ __('No suppliers match your search criteria.') }}
                        <br>
                        {{ __('Try adjusting your search terms.') }}
                    </p>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('View All Suppliers') }}
                    </a>
                @else
                    <p class="text-muted mb-3">{{ __('Start by adding your first supplier to the system.') }}</p>
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('Add First Supplier') }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.btn-group .btn {
    border-radius: 0.375rem !important;
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success alerts after 5 seconds
    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endpush
@endsection