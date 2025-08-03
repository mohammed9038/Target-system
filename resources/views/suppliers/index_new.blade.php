@extends('layouts.app')

@section('title', __('Suppliers'))

@section('content')

<!-- Page Header -->
<x-page_header 
    title="{{ __('Suppliers') }}"
    description="{{ __('Manage your supplier database') }}"
    icon="bi-building">
    
    <x-slot name="actions">
        <x-crud_actions 
            :hasExport="true"
            :hasImport="true" 
            :hasTemplate="true"
            exportOnclick="window.location.href='{{ route('suppliers.export') }}'"
            importOnclick="window.location.href='{{ route('suppliers.import.form') }}'"
            templateHref="{{ route('suppliers.template') }}"
            addButtonText="{{ __('Add Supplier') }}"
            addButtonHref="{{ route('suppliers.create') }}"
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
    title="{{ __('Suppliers List') }}"
    icon="bi-building"
    search-placeholder="{{ __('Search suppliers...') }}"
    :search-value="request('search')"
    search-route="{{ route('suppliers.index') }}"
    :total-records="method_exists($suppliers, 'total') ? $suppliers->total() : count($suppliers)"
    :columns="[
        ['label' => __('Supplier Code'), 'icon' => 'bi-hash', 'sortable' => true],
        ['label' => __('Company Name'), 'icon' => 'bi-building', 'sortable' => true],
        ['label' => __('Contact'), 'icon' => 'bi-person', 'sortable' => true],
        ['label' => __('Status'), 'icon' => 'bi-activity', 'class' => 'text-center', 'align' => 'justify-content-center', 'sortable' => true],
        ['label' => __('Actions'), 'icon' => 'bi-gear', 'class' => 'text-center', 'align' => 'justify-content-center']
    ]"
>
    @forelse($suppliers as $supplier)
        <tr>
            <td>
                <code class="bg-primary-subtle text-primary px-2 py-1 rounded small">{{ $supplier->supplier_code }}</code>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-building text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-medium text-dark">{{ $supplier->company_name }}</div>
                        @if($supplier->email)
                            <small class="text-muted">{{ $supplier->email }}</small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                @if($supplier->contact_person)
                    <div class="fw-medium">{{ $supplier->contact_person }}</div>
                @endif
                @if($supplier->phone)
                    <small class="text-muted">{{ $supplier->phone }}</small>
                @endif
            </td>
            <td class="text-center">
                @if($supplier->is_active)
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
                    <a href="{{ route('suppliers.show', $supplier) }}" 
                       class="btn btn-outline-primary btn-sm"
                       title="{{ __('View') }}">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('suppliers.edit', $supplier) }}" 
                       class="btn btn-outline-warning btn-sm"
                       title="{{ __('Edit') }}">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if(method_exists($supplier, 'toggleStatus'))
                        <form action="{{ route('suppliers.toggle_status', $supplier) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-sm {{ $supplier->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                    title="{{ $supplier->is_active ? __('Deactivate') : __('Activate') }}">
                                <i class="bi {{ $supplier->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                            </button>
                        </form>
                    @endif
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="confirmDelete('{{ $supplier->id }}', '{{ $supplier->company_name }}')"
                            title="{{ __('Delete') }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                
                <!-- Hidden Delete Form -->
                <form id="delete-form-{{ $supplier->id }}" 
                      action="{{ route('suppliers.destroy', $supplier) }}" 
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
                    <i class="bi bi-building display-4 mb-3"></i>
                    <p class="mb-0">{{ __('No suppliers found') }}</p>
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
                {{ __('Showing') }} {{ method_exists($suppliers, 'firstItem') ? $suppliers->firstItem() ?? 0 : 1 }} {{ __('to') }} {{ method_exists($suppliers, 'lastItem') ? $suppliers->lastItem() ?? 0 : count($suppliers) }} 
                {{ __('of') }} {{ method_exists($suppliers, 'total') ? $suppliers->total() : count($suppliers) }} {{ __('results') }}
            </div>
            @if(method_exists($suppliers, 'links'))
                <div>
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </x-slot>
</x-data_table>

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
                <p>{{ __('Are you sure you want to delete the supplier') }} "<span id="deleteSupplierName"></span>"?</p>
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
let deleteSupplierId = null;

function confirmDelete(supplierId, supplierName) {
    deleteSupplierId = supplierId;
    document.getElementById('deleteSupplierName').textContent = supplierName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteSupplierId) {
        document.getElementById('delete-form-' + deleteSupplierId).submit();
    }
});
</script>
@endpush
