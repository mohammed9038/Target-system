@extends('layouts.app')

@section('title', __('Users'))

@section('content')

<!-- Page Header -->
<x-page_header 
    title="{{ __('Users') }}"
    description="{{ __('Manage system users and permissions') }}"
    icon="bi-person-gear">
    
    <x-slot name="actions">
        <x-crud_actions 
            :hasExport="false"
            :hasImport="false" 
            :hasTemplate="false"
            addButtonText="{{ __('Add User') }}"
            addButtonHref="{{ route('users.create') }}"
            addButtonIcon="bi-person-plus"
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

<!-- Users Table -->
<x-card title="{{ __('Users List') }}" icon="bi-person-gear">
    <x-slot name="actions">
        <div class="d-flex align-items-center gap-3">
            <small class="text-muted">
                {{ count($users) }} {{ __('records') }}
            </small>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0" id="searchInput" placeholder="{{ __('Search users...') }}">
            </div>
        </div>
    </x-slot>
    
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="usersTable">
            <thead>
                <tr>
                    <th class="px-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person me-2 text-muted"></i>{{ __('Username') }}
                        </div>
                    </th>
                    <th>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shield me-2 text-muted"></i>{{ __('Role') }}
                        </div>
                    </th>
                    <th>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt me-2 text-muted"></i>{{ __('Region') }}
                        </div>
                    </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-diagram-3 me-2 text-muted"></i>{{ __('Channel') }}
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-tags me-2 text-muted"></i>{{ __('Classification') }}
                            </div>
                        </th>
                        <th class="border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock me-2 text-muted"></i>{{ __('Created') }}
                            </div>
                        </th>
                        <th class="border-0 text-center" style="width: 120px;">
                            <i class="bi bi-gear me-1 text-muted"></i>{{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4">
                                <div class="fw-medium text-dark">
                                    {{ $user->username }}
                                    @if($user->id === auth()->id())
                                        <small class="text-primary ms-2">
                                            <i class="bi bi-star-fill me-1"></i>{{ __('You') }}
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($user->isAdmin())
                                    <span class="badge bg-danger-subtle text-danger px-2">
                                        <i class="bi bi-shield-fill me-1"></i>{{ __('Admin') }}
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info px-2">
                                        <i class="bi bi-person-check me-1"></i>{{ __('Manager') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->region)
                                    <div class="text-muted small">{{ $user->region->name }}</div>
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-dash-circle me-1"></i>{{ __('All Regions') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->channel)
                                    <div class="text-muted small">{{ $user->channel->name }}</div>
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-dash-circle me-1"></i>{{ __('All Channels') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $userClassifications = $user->getClassificationListAttribute();
                                @endphp
                                @if(!empty($userClassifications))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($userClassifications as $classification)
                                            @if($classification === 'food')
                                                <span class="badge bg-success-subtle text-success px-2">
                                                    <i class="bi bi-apple me-1"></i>{{ __('Food') }}
                                                </span>
                                            @elseif($classification === 'non_food')
                                                <span class="badge bg-info-subtle text-info px-2">
                                                    <i class="bi bi-box me-1"></i>{{ __('Non-Food') }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-dash-circle me-1"></i>{{ __('No Classifications') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $user->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('users.show', $user) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="{{ __('View') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="{{ __('Edit') }}"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('{{ __('Are you sure? This will permanently delete the user.') }}')" 
                                                    title="{{ __('Delete') }}"
                                                    data-bs-toggle="tooltip">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-person-gear" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">{{ __('No users found') }}</p>
                                    <small class="text-muted">{{ __('Try adjusting your search or create a new user') }}</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-card>

@push('scripts')
<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
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
</script>
@endpush
@endsection