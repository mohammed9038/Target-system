@extends('layouts.app')

@section('title', __('Edit Supplier'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">{{ __('Edit Supplier') }}</h1>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to Suppliers') }}
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="supplier_code" class="form-label">{{ __('Supplier Code') }}</label>
                            <input type="text" class="form-control" id="supplier_code" value="{{ $supplier->supplier_code }}" readonly>
                            <div class="form-text">
                                <i class="bi bi-lock me-1"></i>{{ __('Supplier code is auto-generated and cannot be changed') }}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Supplier Name') }} *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $supplier->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="classification" class="form-label">{{ __('Classification') }} *</label>
                            <select class="form-select @error('classification') is-invalid @enderror" 
                                    id="classification" name="classification" required>
                                <option value="">{{ __('Select Classification') }}</option>
                                <option value="food" {{ old('classification', $supplier->classification) === 'food' ? 'selected' : '' }}>{{ __('Food') }}</option>
                                <option value="non_food" {{ old('classification', $supplier->classification) === 'non_food' ? 'selected' : '' }}>{{ __('Non-Food') }}</option>
                            </select>
                            @error('classification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('Active') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('Update Supplier') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 