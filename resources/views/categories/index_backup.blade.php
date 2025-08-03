@extends('layouts.app')

@section('title', __('Categories'))

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h2 mb-1">{{ __('Categories') }}</h1>
        <p class="text-muted mb-0">{{ __('Manage product categories') }}</p>
    </div>
    <div>
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
        <h5 class="card-title mb-0">
            <i class="bi bi-tags me-2"></i>{{ __('Categories List') }}
        </h5>
    </div>
    <div class="card-body">
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Category Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Supplier') }}</th>
                            <th>{{ __('Status') }}</th>
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
@endsection
