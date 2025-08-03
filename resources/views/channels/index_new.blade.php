@extends('layouts.app')

@section('title', __('Channels'))

@section('content')

<!-- Page Header -->
<x-page_header 
    title="{{ __('Channels') }}"
    description="{{ __('Manage sales channels') }}"
    icon="bi-diagram-3">
    
    <x-slot name="actions">
        <x-crud_actions 
            :hasExport="true"
            :hasImport="true" 
            :hasTemplate="true"
            exportOnclick="exportChannels()"
            importOnclick="showUploadModal()"
            templateHref="{{ route('channels.template') }}"
            addButtonText="{{ __('Add Channel') }}"
            addButtonHref="{{ route('channels.create') }}"
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
    title="{{ __('Channels List') }}"
    icon="bi-diagram-3"
    search-placeholder="{{ __('Search channels...') }}"
    :search-value="request('search')"
    search-route="{{ route('channels.index') }}"
    :total-records="method_exists($channels, 'total') ? $channels->total() : count($channels)"
    :columns="[
        ['label' => __('Channel Code'), 'icon' => 'bi-hash', 'sortable' => true],
        ['label' => __('Channel Name'), 'icon' => 'bi-diagram-3', 'sortable' => true],
        ['label' => __('Description'), 'icon' => 'bi-card-text'],
        ['label' => __('Status'), 'icon' => 'bi-activity', 'class' => 'text-center', 'align' => 'justify-content-center', 'sortable' => true],
        ['label' => __('Actions'), 'icon' => 'bi-gear', 'class' => 'text-center', 'align' => 'justify-content-center']
    ]"
>
    @forelse($channels as $channel)
        <tr>
            <td>
                <code class="bg-primary-subtle text-primary px-2 py-1 rounded small">{{ $channel->channel_code }}</code>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-diagram-3 text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-medium text-dark">{{ $channel->channel_name }}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="text-muted">{{ $channel->description ?? 'No description' }}</span>
            </td>
            <td class="text-center">
                @if($channel->is_active ?? true)
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
                    <a href="{{ route('channels.show', $channel) }}" 
                       class="btn btn-outline-primary btn-sm"
                       title="{{ __('View') }}">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('channels.edit', $channel) }}" 
                       class="btn btn-outline-warning btn-sm"
                       title="{{ __('Edit') }}">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if(method_exists($channel, 'toggleStatus'))
                        <form action="{{ route('channels.toggle_status', $channel) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-sm {{ ($channel->is_active ?? true) ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                    title="{{ ($channel->is_active ?? true) ? __('Deactivate') : __('Activate') }}">
                                <i class="bi {{ ($channel->is_active ?? true) ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                            </button>
                        </form>
                    @endif
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="confirmDelete('{{ $channel->id }}', '{{ $channel->channel_name }}')"
                            title="{{ __('Delete') }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                
                <!-- Hidden Delete Form -->
                <form id="delete-form-{{ $channel->id }}" 
                      action="{{ route('channels.destroy', $channel) }}" 
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
                    <i class="bi bi-diagram-3 display-4 mb-3"></i>
                    <p class="mb-0">{{ __('No channels found') }}</p>
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
                {{ __('Showing') }} {{ method_exists($channels, 'firstItem') ? $channels->firstItem() ?? 0 : 1 }} {{ __('to') }} {{ method_exists($channels, 'lastItem') ? $channels->lastItem() ?? 0 : count($channels) }} 
                {{ __('of') }} {{ method_exists($channels, 'total') ? $channels->total() : count($channels) }} {{ __('results') }}
            </div>
            @if(method_exists($channels, 'links'))
                <div>
                    {{ $channels->links() }}
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
                    <i class="bi bi-cloud-upload me-2"></i>{{ __('Import Channels') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('channels.import') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
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
                <p>{{ __('Are you sure you want to delete the channel') }} "<span id="deleteChannelName"></span>"?</p>
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
let deleteChannelId = null;

function showUploadModal() {
    const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
    uploadModal.show();
}

function confirmDelete(channelId, channelName) {
    deleteChannelId = channelId;
    document.getElementById('deleteChannelName').textContent = channelName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteChannelId) {
        document.getElementById('delete-form-' + deleteChannelId).submit();
    }
});

function exportChannels() {
    const searchParams = new URLSearchParams(window.location.search);
    const exportUrl = new URL('{{ route("channels.export") }}', window.location.origin);
    
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
