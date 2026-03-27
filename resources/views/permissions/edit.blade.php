@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Edit Permission
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('permissions.update', $permission) }}">
                    @csrf
                    @method('PUT')

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
                            value="{{ old('name', $permission->name) }}"
                            required
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
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
