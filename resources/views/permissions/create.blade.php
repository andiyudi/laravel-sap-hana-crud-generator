@extends('layouts.app')

@section('title', 'Add Permission')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-key-fill me-2"></i>
                    Add New Permission
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('permissions.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-tag me-1"></i>
                            Permission Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="e.g., product.create, user.edit"
                        >
                        <small class="text-muted">Use dot notation: resource.action</small>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Create Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
